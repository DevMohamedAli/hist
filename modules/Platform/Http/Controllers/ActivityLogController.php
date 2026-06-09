<?php

namespace Modules\Platform\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;
use Inertia\Response as InertiaResponse;
use Maatwebsite\Excel\Facades\Excel;
use Modules\Academic\Models\Specialization;
use Modules\Platform\Exports\ActivitiesExport;
use Modules\User\Models\User;
use Modules\Shared\Http\Controllers\Controller;
use Spatie\Activitylog\Models\Activity;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Modules\Platform\Models\ActivityLogView;
use Barryvdh\DomPDF\Facade\Pdf;
use Symfony\Component\HttpFoundation\StreamedResponse;


class ActivityLogController extends Controller
{
    /**
     * Display the activity log index page (Inertia).
     */
    public function index(Request $request): InertiaResponse
    {
        Gate::authorize('viewActivityLogUi');

        $filters = $request->validate([
            'search'    => 'nullable|string|max:255',
            'event'     => 'nullable|string|max:50',
            'causer_id' => 'nullable|integer|exists:users,id',
            'date_from' => 'nullable|date',
            'date_to'   => 'nullable|date|after_or_equal:date_from',
            'per_page'  => 'nullable|integer|in:10,25,50,100',
        ]);

        $perPage = $filters['per_page'] ?? 25;

        $query = Activity::with('causer');

        if (!empty($filters['search'])) {
            $search = '%' . addcslashes($filters['search'], '%_') . '%';
            $query->where(function ($q) use ($search) {
                $q->where('description', 'like', $search)
                    ->orWhere('log_name', 'like', $search)
                    ->orWhere('event', 'like', $search);
            });
        }

        if (!empty($filters['event'])) {
            $query->where('event', $filters['event']);
        }

        if (!empty($filters['causer_id'])) {
            $query->where('causer_id', $filters['causer_id'])
                ->where('causer_type', User::class);
        }

        if (!empty($filters['date_from'])) {
            $query->whereDate('created_at', '>=', $filters['date_from']);
        }
        if (!empty($filters['date_to'])) {
            $query->whereDate('created_at', '<=', $filters['date_to']);
        }

        $activities = $query->orderBy('created_at', 'desc')->paginate($perPage);

        // Transform each activity for frontend
        $activities->getCollection()->transform(function ($activity) {
            return [
                'id'           => $activity->id,
                'log_name'     => $activity->log_name,
                'description'  => $activity->description,
                'event'        => $activity->event,
                'causer'       => $activity->causer ? [
                    'id'    => $activity->causer->id,
                    'name'  => $activity->causer->name,
                    'email' => $activity->causer->email,
                ] : null,
                'subject_type' => $activity->subject_type,
                'subject_id'   => $activity->subject_id,
                'properties'   => $activity->properties,
                'created_at'   => $activity->created_at->toISOString(),
            ];
        });

        $eventTypes = Activity::distinct('event')->whereNotNull('event')->pluck('event')->filter()->values();
        $causers = User::whereHas('activities')->get(['id', 'name', 'email']);

        return Inertia::render('Platform/ActivityLog/Index', [
            'activities' => $activities,
            'filters'    => $filters,
            'eventTypes' => $eventTypes,
            'causers'    => $causers,
            'specializations' => Specialization::query()
                ->orderBy('name')
                ->get(['id', 'name']),
        ]);
    }

    /**
     * Export activities in the requested format (CSV, Excel, PDF).
     */


    public function export(Request $request)
    {
        Gate::authorize('viewActivityLogUi');

        $request->validate([
            'format'  => 'required|in:csv,pdf',
            'filters' => 'nullable|array',
        ]);

        $filters = $request->input('filters', []);
        $format = $request->input('format');

        $query = Activity::with('causer');
        $this->applyFiltersToQuery($query, $filters);
        $activities = $query->orderBy('created_at', 'desc')->get();

        if ($format === 'pdf') {
            $pdf = Pdf::loadView('exports.activities-pdf', [
                'activities'   => $activities,
                'generated_at' => now(),
                'filters'      => $filters,
            ]);
            return $pdf->download('activities.pdf');
        }

        // CSV Export (Native PHP)
        $filename = 'activities_' . now()->format('Y-m-d_His') . '.csv';
        $headers = [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $callback = function () use ($activities) {
            $file = fopen('php://output', 'w');
            // Write BOM for UTF-8 (Excel compatibility)
            fprintf($file, chr(0xEF) . chr(0xBB) . chr(0xBF));
            // Headers in Arabic
            fputcsv($file, ['#', 'الحدث', 'الوصف', 'المستخدم', 'الموضوع', 'المعرف', 'الخصائص', 'التاريخ']);
            foreach ($activities as $i => $activity) {
                fputcsv($file, [
                    $i + 1,
                    $activity->event,
                    $activity->description,
                    $activity->causer?->name ?? 'النظام',
                    $activity->subject_type ? class_basename($activity->subject_type) : '',
                    $activity->subject_id,
                    json_encode($activity->properties, JSON_UNESCAPED_UNICODE),
                    $activity->created_at->format('Y-m-d H:i:s'),
                ]);
            }
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
    /**
     * Get analytics data (stats, event types, top users, timeline, chart data).
     */
    public function analytics(Request $request)
    {
        Gate::authorize('viewActivityLogUi');

        $filters = $request->validate([
            'period'     => 'nullable|in:today,7,30,90',
            'start_date' => 'nullable|date',
            'end_date'   => 'nullable|date|after_or_equal:start_date',
            'event'      => 'nullable|string',
            'causer_id'  => 'nullable|integer|exists:users,id',
        ]);

        $query = Activity::query();
        $this->applyFiltersToQuery($query, $filters);

        // Total activities
        $totalActivities = (clone $query)->count();

        // Today's activities
        $today = now()->toDateString();
        $activitiesToday = (clone $query)->whereDate('created_at', $today)->count();

        // This week (last 7 days)
        $weekStart = now()->subDays(7);
        $activitiesThisWeek = (clone $query)->whereDate('created_at', '>=', $weekStart)->count();

        // This month (last 30 days)
        $monthStart = now()->subDays(30);
        $activitiesThisMonth = (clone $query)->whereDate('created_at', '>=', $monthStart)->count();

        // Event types with counts & percentages
        $eventTypes = (clone $query)
            ->select('event', DB::raw('count(*) as count'))
            ->whereNotNull('event')
            ->groupBy('event')
            ->get()
            ->map(function ($item) use ($totalActivities) {
                return [
                    'name'       => $item->event,
                    'count'      => $item->count,
                    'percentage' => $totalActivities ? round(($item->count / $totalActivities) * 100, 1) : 0,
                    'color'      => $this->getEventColor($item->event),
                ];
            });

        // Top users (causers)
        $topUsers = (clone $query)
            ->select('causer_id', DB::raw('count(*) as activity_count'))
            ->whereNotNull('causer_id')
            ->where('causer_type', User::class)
            ->groupBy('causer_id')
            ->orderByDesc('activity_count')
            ->limit(10)
            ->get()
            ->map(function ($item) {
                $user = User::find($item->causer_id);
                return [
                    'id'             => $user?->id,
                    'name'           => $user?->name ?? 'مستخدم محذوف',
                    'email'          => $user?->email ?? '',
                    'activity_count' => $item->activity_count,
                ];
            });

        // Timeline (last 30 days grouped by date)
        $timelineStart = now()->subDays(30);
        $timeline = (clone $query)
            ->select(DB::raw('DATE(created_at) as date'), DB::raw('count(*) as count'))
            ->whereDate('created_at', '>=', $timelineStart)
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->map(function ($item) {
                return [
                    'date'      => $item->date,
                    'day_name'  => \Carbon\Carbon::parse($item->date)->translatedFormat('l'),
                    'count'     => $item->count,
                ];
            });

        // Calculate max count for percentage
        $maxCount = $timeline->max('count') ?: 1;
        $timeline = $timeline->map(function ($item) use ($maxCount) {
            $item['percentage'] = round(($item['count'] / $maxCount) * 100, 1);
            return $item;
        });

        // Popular models (subject_type)
        $popularModels = (clone $query)
            ->select('subject_type', DB::raw('count(*) as activity_count'))
            ->whereNotNull('subject_type')
            ->groupBy('subject_type')
            ->orderByDesc('activity_count')
            ->limit(10)
            ->get()
            ->map(function ($item) {
                return [
                    'type'           => $item->subject_type,
                    'name'           => class_basename($item->subject_type),
                    'activity_count' => $item->activity_count,
                ];
            });

        // Activity trends for chart (last 30 days, grouped by event type)
        $trendsStart = now()->subDays(30);
        $rawTrends = (clone $query)
            ->select(DB::raw('DATE(created_at) as date'), 'event', DB::raw('count(*) as count'))
            ->whereDate('created_at', '>=', $trendsStart)
            ->whereNotNull('event')
            ->groupBy('date', 'event')
            ->orderBy('date')
            ->get();

        $dates = $rawTrends->pluck('date')->unique()->sort()->values();
        $eventGroups = $rawTrends->groupBy('event');
        $datasets = [];
        foreach ($eventGroups as $event => $rows) {
            $data = [];
            foreach ($dates as $date) {
                $row = $rows->firstWhere('date', $date);
                $data[] = $row ? $row->count : 0;
            }
            $datasets[] = [
                'label' => $event,
                'data'  => $data,
                'color' => $this->getEventColor($event),
            ];
        }

        return response()->json([
            'success' => true,
            'data' => [
                'total_activities'        => $totalActivities,
                'activities_today'        => $activitiesToday,
                'activities_this_week'    => $activitiesThisWeek,
                'activities_this_month'   => $activitiesThisMonth,
                'event_types'             => $eventTypes,
                'top_users'               => $topUsers,
                'timeline'                => $timeline,
                'popular_models'          => $popularModels,
                'activity_trends'         => [
                    'dates'    => $dates,
                    'datasets' => $datasets,
                ],
            ],
        ]);
    }

    /**
     * Get filter options (causers, event types, subject types).
     */
    public function filterOptions()
    {
        Gate::authorize('viewActivityLogUi');

        $causers = User::whereHas('activities')->get(['id', 'name', 'email']);
        $eventTypes = Activity::distinct('event')->whereNotNull('event')->pluck('event')->filter()->values();
        $subjectTypes = Activity::distinct('subject_type')->whereNotNull('subject_type')->pluck('subject_type')->filter()->values();

        // Transform subject types to label/value pairs
        $subjectTypes = $subjectTypes->map(function ($type) {
            return [
                'value' => $type,
                'label' => class_basename($type),
            ];
        });

        return response()->json([
            'success' => true,
            'data' => [
                'causers'        => $causers,
                'event_types'    => $eventTypes->map(fn($e) => ['value' => $e, 'label' => ucfirst($e)]),
                'subject_types'  => $subjectTypes,
            ],
        ]);
    }

    /**
     * List saved views for the authenticated user.
     */
    public function listViews(Request $request)
    {
        Gate::authorize('viewActivityLogUi');
        $user = $request->user();

        // Assuming a 'saved_views' table or using a JSON column on users
        // For simplicity, we store in a separate table 'activity_log_views'
        $views = ActivityLogView::where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json(['success' => true, 'data' => $views]);
    }

    /**
     * Save a new view.
     */
    public function saveView(Request $request)
    {
        Gate::authorize('viewActivityLogUi');
        $request->validate([
            'name'    => 'required|string|max:255',
            'filters' => 'nullable|array',
        ]);

        $view = \Modules\Platform\Models\ActivityLogView::create([
            'user_id' => $request->user()->id,
            'name'    => $request->name,
            'filters' => $request->filters ?? [],
        ]);

        return response()->json(['success' => true, 'message' => 'تم حفظ العرض بنجاح', 'data' => $view]);
    }

    /**
     * Delete a saved view.
     */
    public function deleteView(Request $request)
    {
        Gate::authorize('viewActivityLogUi');
        $request->validate(['view_id' => 'required|integer|exists:activity_log_views,id']);

        $view = \Modules\Platform\Models\ActivityLogView::where('id', $request->view_id)
            ->where('user_id', $request->user()->id)
            ->firstOrFail();

        $view->delete();

        return response()->json(['success' => true, 'message' => 'تم حذف العرض بنجاح']);
    }

    /**
     * Helper: apply filters to a query builder.
     */
    private function applyFiltersToQuery(\Illuminate\Database\Eloquent\Builder $query, array $filters): void
    {
        if (!empty($filters['search'])) {
            $search = '%' . addcslashes($filters['search'], '%_') . '%';
            $query->where(function ($q) use ($search) {
                $q->where('description', 'like', $search)
                    ->orWhere('log_name', 'like', $search)
                    ->orWhere('event', 'like', $search);
            });
        }

        if (!empty($filters['event'])) {
            $query->where('event', $filters['event']);
        }

        if (!empty($filters['causer_id'])) {
            $query->where('causer_id', $filters['causer_id'])
                ->where('causer_type', User::class);
        }

        if (!empty($filters['date_from'])) {
            $query->whereDate('created_at', '>=', $filters['date_from']);
        }
        if (!empty($filters['date_to'])) {
            $query->whereDate('created_at', '<=', $filters['date_to']);
        }

        if (!empty($filters['period'])) {
            $start = match ($filters['period']) {
                'today' => now()->startOfDay(),
                '7'     => now()->subDays(7),
                '30'    => now()->subDays(30),
                '90'    => now()->subDays(90),
                default => null,
            };
            if ($start) {
                $query->whereDate('created_at', '>=', $start);
            }
        }
    }

    /**
     * Helper: get a consistent color for event types.
     */
    private function getEventColor(string $event): string
    {
        return match ($event) {
            'created'  => '#10b981', // green
            'updated'  => '#3b82f6', // blue
            'deleted'  => '#ef4444', // red
            'restored' => '#f59e0b', // yellow
            'login'    => '#8b5cf6', // purple
            'logout'   => '#6b7280', // gray
            default    => '#f97316', // orange
        };
    }
}

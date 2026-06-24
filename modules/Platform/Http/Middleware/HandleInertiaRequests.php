<?php

namespace Modules\Platform\Http\Middleware;

use Illuminate\Http\Request;
use Inertia\Middleware;
use Modules\Academic\Models\AcademicSemester;
use Modules\Student\Actions\GetPublicRegistrationStatus;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that's loaded on the first page visit.
     *
     * @see https://inertiajs.com/server-side-setup#root-template
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determines the current asset version.
     *
     * @see https://inertiajs.com/asset-versioning
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @see https://inertiajs.com/shared-data
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        $registration = app(GetPublicRegistrationStatus::class)->execute();

        if (! $registration['is_open']) {
            $currentSemester = AcademicSemester::currentAcademicSemester();
            $registration['message'] = $currentSemester
                ? 'التسجيل مغلق حالياً. نافذة '.$currentSemester->code.' انتهت في '.$currentSemester->registration_end?->format('Y-m-d').'.'
                : 'التسجيل مغلق حالياً لعدم وجود فصل دراسي مفتوح.';
        } else {
            $registration['message'] = 'التسجيل مفتوح حتى '.$registration['semester']['registration_end'].'.';
        }

        return [
            ...parent::share($request),
            'name' => config('app.name'),
            'registration' => $registration,
            'auth' => [
                'user' => fn () => $request->user()
                    ? [
                        'id' => $request->user()->id,
                        'name' => $request->user()->name,
                        'email' => $request->user()->email,
                        'avatar' => $request->user()->avatar
                            ? asset('storage/'.$request->user()->avatar)
                            : null,
                        'email_verified_at' => $request->user()->email_verified_at,
                        'roles' => $request->user()->getRoleNames()
                            ->map(fn (string $role): array => ['name' => $role])
                            ->values(),
                        'permissions' => $request->user()->getAllPermissions()
                            ->map(fn ($permission): array => ['name' => $permission->name])
                            ->values(),
                    ]
                    : null,
            ],
            'sidebarOpen' => ! $request->hasCookie('sidebar_state') || $request->cookie('sidebar_state') === 'true',
            'flash' => [
                'success' => fn () => $request->session()->get('success'),
                'error' => fn () => $request->session()->get('error'),
                'message' => fn () => $request->session()->get('message'),
                'enrollment_id' => fn () => $request->session()->get('enrollment_id'),
                'total_mark' => fn () => $request->session()->get('total_mark'),
                'grade_evaluation' => fn () => $request->session()->get('grade_evaluation'),
                'status' => fn () => $request->session()->get('status'),
            ],
        ];
    }
}

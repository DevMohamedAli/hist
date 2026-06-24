<?php

namespace Modules\Correspondence\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;
use Modules\Correspondence\Actions\AddCorrespondenceRecipient;
use Modules\Correspondence\Actions\ApproveCorrespondence;
use Modules\Correspondence\Actions\ArchiveCorrespondence;
use Modules\Correspondence\Actions\CompleteCorrespondence;
use Modules\Correspondence\Actions\CreateCorrespondence;
use Modules\Correspondence\Actions\DispatchCorrespondence;
use Modules\Correspondence\Actions\ReplyToCorrespondence;
use Modules\Correspondence\Actions\SubmitCorrespondence;
use Modules\Correspondence\Enums\CorrespondenceRecipientType;
use Modules\Correspondence\Http\Requests\ReplyCorrespondenceRequest;
use Modules\Correspondence\Http\Requests\StoreCorrespondenceRequest;
use Modules\Correspondence\Models\Correspondence;
use Modules\Correspondence\Models\CorrespondenceCategory;
use Modules\Shared\Http\Controllers\Controller;

class CorrespondenceController extends Controller
{
    public function inbox(Request $request): Response
    {
        Gate::authorize('viewAny', Correspondence::class);

        $filters = $request->only(['search', 'status', 'priority', 'classification']);

        return Inertia::render('Correspondence/Inbox', [
            'correspondences' => Correspondence::query()
                ->with(['sender:id,name', 'category:id,name'])
                ->visibleTo($request->user())
                ->whereHas('recipients', fn ($query) => $query->where('user_id', $request->user()->id))
                ->when($filters['search'] ?? null, fn ($query, $search) => $query->where(function ($query) use ($search): void {
                    $query
                        ->where('reference_number', 'like', "%{$search}%")
                        ->orWhere('subject', 'like', "%{$search}%");
                }))
                ->when($filters['status'] ?? null, fn ($query, $status) => $query->where('status', $status))
                ->when($filters['priority'] ?? null, fn ($query, $priority) => $query->where('priority', $priority))
                ->when($filters['classification'] ?? null, fn ($query, $classification) => $query->where('classification', $classification))
                ->latest()
                ->paginate(15)
                ->withQueryString(),
            'filters' => $filters,
        ]);
    }

    public function sent(Request $request): Response
    {
        Gate::authorize('viewAny', Correspondence::class);

        $filters = $request->only(['search', 'status', 'priority', 'classification']);

        return Inertia::render('Correspondence/Sent', [
            'correspondences' => Correspondence::query()
                ->with('category:id,name')
                ->where('sender_user_id', $request->user()->id)
                ->when($filters['search'] ?? null, fn ($query, $search) => $query->where(function ($query) use ($search): void {
                    $query
                        ->where('reference_number', 'like', "%{$search}%")
                        ->orWhere('subject', 'like', "%{$search}%");
                }))
                ->when($filters['status'] ?? null, fn ($query, $status) => $query->where('status', $status))
                ->when($filters['priority'] ?? null, fn ($query, $priority) => $query->where('priority', $priority))
                ->when($filters['classification'] ?? null, fn ($query, $classification) => $query->where('classification', $classification))
                ->latest()
                ->paginate(15)
                ->withQueryString(),
            'filters' => $filters,
        ]);
    }

    public function create(): Response
    {
        Gate::authorize('create', Correspondence::class);

        return Inertia::render('Correspondence/Create', [
            'categories' => CorrespondenceCategory::query()->where('is_active', true)->orderBy('name')->get(),
        ]);
    }

    public function store(
        StoreCorrespondenceRequest $request,
        CreateCorrespondence $create,
        AddCorrespondenceRecipient $addRecipient,
    ): RedirectResponse {
        $correspondence = $create->execute($request->user(), $request->validated());

        foreach ($request->validated('recipients', []) as $recipient) {
            $addRecipient->execute($correspondence, (int) $recipient['user_id'], $recipient['recipient_type']);
        }

        return redirect()->route('correspondence.show', $correspondence)->with('success', 'Draft correspondence created.');
    }

    public function show(Request $request, Correspondence $correspondence): Response
    {
        Gate::authorize('view', $correspondence);

        $recipient = $correspondence->recipients()->where('user_id', $request->user()->id)->first();
        if ($recipient && ! $recipient->read_at) {
            $recipient->update(['read_at' => now()]);
        }

        $correspondence->load(['sender:id,name', 'category:id,name', 'recipients.user:id,name', 'attachments', 'replies.sender:id,name', 'statusHistories']);

        if ((int) $correspondence->sender_user_id !== (int) $request->user()->id && ! $request->user()->can('correspondence.admin')) {
            $correspondence->setRelation(
                'recipients',
                $correspondence->recipients
                    ->filter(fn ($recipient): bool => $recipient->recipient_type !== CorrespondenceRecipientType::Bcc || (int) $recipient->user_id === (int) $request->user()->id)
                    ->values(),
            );
        }

        return Inertia::render('Correspondence/Show', [
            'correspondence' => $correspondence,
        ]);
    }

    public function submit(Request $request, Correspondence $correspondence, SubmitCorrespondence $submit): RedirectResponse
    {
        Gate::authorize('submit', $correspondence);
        $submit->execute($correspondence, $request->user());

        return back()->with('success', 'Correspondence submitted.');
    }

    public function approve(Request $request, Correspondence $correspondence, ApproveCorrespondence $approve): RedirectResponse
    {
        Gate::authorize('approve', $correspondence);
        $approve->execute($correspondence, $request->user(), $request, $request->input('notes'));

        return back()->with('success', 'Correspondence approved.');
    }

    public function dispatch(Request $request, Correspondence $correspondence, DispatchCorrespondence $dispatch): RedirectResponse
    {
        Gate::authorize('dispatch', $correspondence);
        $dispatch->execute($correspondence, $request->user());

        return back()->with('success', 'Correspondence dispatched.');
    }

    public function reply(ReplyCorrespondenceRequest $request, Correspondence $correspondence, ReplyToCorrespondence $reply): RedirectResponse
    {
        Gate::authorize('reply', $correspondence);
        $reply->execute($correspondence, $request->user(), $request->validated('body'));

        return back()->with('success', 'Reply added.');
    }

    public function complete(Request $request, Correspondence $correspondence, CompleteCorrespondence $complete): RedirectResponse
    {
        Gate::authorize('complete', $correspondence);
        $complete->execute($correspondence, $request->user());

        return back()->with('success', 'Correspondence completed.');
    }

    public function archive(Request $request, Correspondence $correspondence, ArchiveCorrespondence $archive): RedirectResponse
    {
        Gate::authorize('archive', $correspondence);
        $archive->execute($correspondence, $request->user());

        return back()->with('success', 'Correspondence archived.');
    }
}

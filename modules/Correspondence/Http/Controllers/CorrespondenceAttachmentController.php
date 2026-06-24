<?php

namespace Modules\Correspondence\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Modules\Correspondence\Actions\AttachCorrespondenceFile;
use Modules\Correspondence\Http\Requests\StoreCorrespondenceAttachmentRequest;
use Modules\Correspondence\Models\Correspondence;
use Modules\Correspondence\Models\CorrespondenceAttachment;
use Modules\Shared\Http\Controllers\Controller;
use Symfony\Component\HttpFoundation\StreamedResponse;

class CorrespondenceAttachmentController extends Controller
{
    public function store(
        StoreCorrespondenceAttachmentRequest $request,
        Correspondence $correspondence,
        AttachCorrespondenceFile $attach,
    ): RedirectResponse {
        Gate::authorize('reply', $correspondence);

        $attach->execute(
            $correspondence,
            $request->user(),
            $request->file('file'),
            $request->validated('description'),
        );

        return back()->with('success', 'Attachment uploaded.');
    }

    public function show(CorrespondenceAttachment $attachment): StreamedResponse
    {
        Gate::authorize('view', $attachment->correspondence);

        abort_unless(
            Storage::disk($attachment->storage_disk)->exists($attachment->stored_filename),
            404,
        );

        return Storage::disk($attachment->storage_disk)->download(
            $attachment->stored_filename,
            $attachment->original_filename,
        );
    }
}

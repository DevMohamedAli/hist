<?php

namespace Modules\Website\Http\Controllers\Public;

use Illuminate\Http\RedirectResponse;
use Modules\Shared\Http\Controllers\Controller;
use Modules\Website\Http\Requests\StoreContactSubmissionRequest;
use Modules\Website\Models\ContactSubmission;

class ContactController extends Controller
{
    public function store(StoreContactSubmissionRequest $request): RedirectResponse
    {
        ContactSubmission::create([
            ...$request->validated(),
            'ip_address' => $request->ip(),
            'user_agent' => substr((string) $request->userAgent(), 0, 1000),
        ]);

        return back()->with('success', 'Contact request received.');
    }
}

<?php

namespace Modules\Website\Http\Controllers\Admin;

use Inertia\Inertia;
use Inertia\Response;
use Modules\Shared\Http\Controllers\Controller;
use Modules\Website\Models\ContactSubmission;

class ContactSubmissionController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('Website/Admin/ContactSubmissions', [
            'submissions' => ContactSubmission::query()
                ->latest()
                ->paginate(20),
        ]);
    }
}

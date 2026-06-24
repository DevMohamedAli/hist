<?php

namespace Modules\Website\Http\Controllers\Admin;

use Inertia\Inertia;
use Inertia\Response;
use Modules\Shared\Http\Controllers\Controller;
use Modules\Website\Models\Banner;
use Modules\Website\Models\ContactSubmission;
use Modules\Website\Models\Faq;
use Modules\Website\Models\Page;
use Modules\Website\Models\Post;

class DashboardController extends Controller
{
    public function __invoke(): Response
    {
        return Inertia::render('Website/Admin/Dashboard', [
            'stats' => [
                'pages' => Page::query()->count(),
                'posts' => Post::query()->count(),
                'banners' => Banner::query()->count(),
                'faqs' => Faq::query()->count(),
                'contactSubmissions' => ContactSubmission::query()->count(),
            ],
        ]);
    }
}

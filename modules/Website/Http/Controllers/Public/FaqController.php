<?php

namespace Modules\Website\Http\Controllers\Public;

use Inertia\Inertia;
use Inertia\Response;
use Modules\Shared\Http\Controllers\Controller;
use Modules\Website\Models\Faq;

class FaqController extends Controller
{
    public function __invoke(): Response
    {
        return Inertia::render('Website/Public/Faqs', [
            'faqs' => Faq::query()
                ->where('is_published', true)
                ->orderBy('sort_order')
                ->latest()
                ->get(['id', 'question', 'answer']),
        ]);
    }
}

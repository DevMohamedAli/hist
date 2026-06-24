<?php

namespace Modules\Website\Http\Controllers\Admin;

use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;
use Modules\Shared\Http\Controllers\Controller;
use Modules\Website\Http\Requests\StoreFaqRequest;
use Modules\Website\Http\Requests\UpdateFaqRequest;
use Modules\Website\Models\Faq;

class FaqController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('Website/Admin/Faqs', [
            'faqs' => Faq::query()
                ->orderBy('sort_order')
                ->latest()
                ->paginate(15),
        ]);
    }

    public function store(StoreFaqRequest $request): RedirectResponse
    {
        Faq::create([
            ...$request->validated(),
            'sort_order' => $request->integer('sort_order'),
            'is_published' => $request->boolean('is_published'),
        ]);

        return back()->with('success', 'FAQ created.');
    }

    public function update(UpdateFaqRequest $request, Faq $faq): RedirectResponse
    {
        $faq->update([
            ...$request->validated(),
            'sort_order' => $request->integer('sort_order'),
            'is_published' => $request->boolean('is_published'),
        ]);

        return back()->with('success', 'FAQ updated.');
    }
}

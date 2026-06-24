<?php

namespace Modules\Website\Http\Controllers\Admin;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Modules\Shared\Http\Controllers\Controller;
use Modules\Website\Http\Requests\StorePageRequest;
use Modules\Website\Http\Requests\UpdatePageRequest;
use Modules\Website\Models\Page;
use Modules\Website\Support\WebsiteImageManager;

class PageController extends Controller
{
    public function __construct(private readonly WebsiteImageManager $images) {}

    public function index(): Response
    {
        $aboutPage = Page::query()->where('slug', 'about')->first();

        return Inertia::render('Website/Admin/Pages', [
            'page' => $aboutPage,
            'canPublish' => request()->user()?->can('website.pages.publish') ?? false,
        ]);
    }

    public function store(StorePageRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        $aboutPage = Page::query()->where('slug', 'about')->first();
        $validated = $this->storeFeaturedImage($request, $validated, $aboutPage);

        if ($validated['status'] !== 'published') {
            $validated['published_at'] = null;
        } elseif (! $request->user()->can('website.pages.publish')) {
            return back()->with('error', 'لا تملك صلاحية نشر صفحة عن المعهد. احفظها كمسودة أو اطلب صلاحية النشر.');
        }

        Page::query()->updateOrCreate(['slug' => 'about'], [
            ...$validated,
            'slug' => 'about',
            'created_by' => $request->user()->id,
            'updated_by' => $request->user()->id,
        ]);

        return back()->with('success', 'تم حفظ صفحة عن المعهد.');
    }

    public function update(UpdatePageRequest $request, Page $page): RedirectResponse
    {
        abort_unless($page->slug === 'about', 404);

        $validated = $request->validated();
        $validated = $this->storeFeaturedImage($request, $validated, $page);

        if ($validated['status'] !== 'published') {
            $validated['published_at'] = null;
        } elseif (! $request->user()->can('website.pages.publish')) {
            return back()->with('error', 'لا تملك صلاحية نشر صفحة عن المعهد. احفظها كمسودة أو اطلب صلاحية النشر.');
        }

        $page->update([
            ...$validated,
            'slug' => 'about',
            'updated_by' => $request->user()->id,
        ]);

        return back()->with('success', 'تم تحديث صفحة عن المعهد.');
    }

    private function storeFeaturedImage(Request $request, array $validated, ?Page $page = null): array
    {
        if (! $request->hasFile('featured_image')) {
            unset($validated['featured_image'], $validated['featured_image_path']);

            return $validated;
        }

        $validated['featured_image_path'] = $this->images->replace(
            $page?->featured_image_path,
            $request->file('featured_image'),
            'website/pages',
        );

        unset($validated['featured_image']);

        return $validated;
    }
}

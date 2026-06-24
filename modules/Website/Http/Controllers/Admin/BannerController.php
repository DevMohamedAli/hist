<?php

namespace Modules\Website\Http\Controllers\Admin;

use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;
use Modules\Shared\Http\Controllers\Controller;
use Modules\Website\Http\Requests\StoreBannerRequest;
use Modules\Website\Http\Requests\UpdateBannerRequest;
use Modules\Website\Models\Banner;
use Modules\Website\Support\WebsiteImageManager;

class BannerController extends Controller
{
    public function __construct(private readonly WebsiteImageManager $images) {}

    public function index(): Response
    {
        return Inertia::render('Website/Admin/Banners', [
            'banners' => Banner::query()
                ->orderBy('sort_order')
                ->latest()
                ->paginate(15),
        ]);
    }

    public function store(StoreBannerRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        if ($request->hasFile('image')) {
            $validated['image_path'] = $this->images->store($request->file('image'), 'website/banners');
        }

        unset($validated['image']);

        Banner::create($this->payload($validated, $request->boolean('is_active')));

        return back()->with('success', 'Banner created.');
    }

    public function update(UpdateBannerRequest $request, Banner $banner): RedirectResponse
    {
        $validated = $request->validated();

        if ($request->hasFile('image')) {
            $validated['image_path'] = $this->images->replace(
                $banner->image_path,
                $request->file('image'),
                'website/banners',
            );
        } else {
            unset($validated['image_path']);
        }

        unset($validated['image']);

        $banner->update($this->payload($validated, $request->boolean('is_active')));

        return back()->with('success', 'Banner updated.');
    }

    private function payload(array $validated, bool $isActive): array
    {
        return [
            ...$validated,
            'is_active' => $isActive,
            'sort_order' => $validated['sort_order'] ?? 0,
        ];
    }
}

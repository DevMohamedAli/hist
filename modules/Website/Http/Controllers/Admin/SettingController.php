<?php

namespace Modules\Website\Http\Controllers\Admin;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Modules\Shared\Http\Controllers\Controller;
use Modules\Website\Models\WebsiteSetting;
use Modules\Website\Support\WebsiteImageManager;

class SettingController extends Controller
{
    public function __construct(private readonly WebsiteImageManager $images) {}

    public function index(): Response
    {
        $settings = WebsiteSetting::query()
            ->orderBy('key')
            ->get()
            ->pluck('value', 'key');

        return Inertia::render('Website/Admin/Settings', [
            'settings' => [
                ...$settings->all(),
                'hero_image_url' => $this->images->url($settings->get('hero_image_path')),
            ],
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        abort_unless($request->user()?->can('website.settings.manage'), 403);

        $validated = $request->validate([
            'site_name' => ['nullable', 'string', 'max:255'],
            'site_description' => ['nullable', 'string', 'max:1000'],
            'contact_email' => ['nullable', 'email', 'max:255'],
            'contact_phone' => ['nullable', 'string', 'max:50'],
            'contact_address' => ['nullable', 'string', 'max:500'],
            'hero_title' => ['nullable', 'string', 'max:255'],
            'hero_subtitle' => ['nullable', 'string', 'max:1000'],
            'hero_image_path' => ['nullable', 'string', 'max:255'],
            'hero_image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
        ]);

        if ($request->hasFile('hero_image')) {
            $previousHeroImage = WebsiteSetting::query()
                ->where('key', 'hero_image_path')
                ->value('value');

            $validated['hero_image_path'] = $this->images->replace(
                $previousHeroImage,
                $request->file('hero_image'),
                'website/settings',
            );
        }

        unset($validated['hero_image']);

        foreach ($validated as $key => $value) {
            WebsiteSetting::query()->updateOrCreate(['key' => $key], ['value' => $value]);
        }

        return back()->with('success', 'Website settings updated.');
    }
}

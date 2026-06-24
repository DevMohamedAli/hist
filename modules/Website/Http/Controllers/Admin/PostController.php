<?php

namespace Modules\Website\Http\Controllers\Admin;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Modules\Shared\Http\Controllers\Controller;
use Modules\Website\Http\Requests\StorePostRequest;
use Modules\Website\Http\Requests\UpdatePostRequest;
use Modules\Website\Models\Post;
use Modules\Website\Support\WebsiteImageManager;

class PostController extends Controller
{
    public function __construct(private readonly WebsiteImageManager $images) {}

    public function index(Request $request): Response
    {
        $type = $request->input('type');
        $status = $request->input('status');

        return Inertia::render('Website/Admin/Posts', [
            'posts' => Post::query()
                ->when($type, fn ($query) => $query->where('type', $type))
                ->when($status, fn ($query) => $query->where('status', $status))
                ->latest()
                ->paginate(15)
                ->withQueryString(),
            'filters' => [
                'type' => $type,
                'status' => $status,
            ],
            'canPublish' => $request->user()?->can('website.posts.publish') ?? false,
        ]);
    }

    public function store(StorePostRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        $validated = $this->storeFeaturedImage($request, $validated);

        if ($validated['status'] !== 'published') {
            $validated['published_at'] = null;
        } elseif (! $request->user()->can('website.posts.publish')) {
            return back()->with('error', 'لا تملك صلاحية نشر الأخبار. احفظ المحتوى كمسودة أو اطلب صلاحية النشر.');
        }

        Post::create([
            ...$validated,
            'created_by' => $request->user()->id,
            'updated_by' => $request->user()->id,
        ]);

        return back()->with('success', 'Website content created.');
    }

    public function update(UpdatePostRequest $request, Post $post): RedirectResponse
    {
        $validated = $request->validated();
        $validated = $this->storeFeaturedImage($request, $validated, $post);

        if ($validated['status'] !== 'published') {
            $validated['published_at'] = null;
        } elseif (! $request->user()->can('website.posts.publish')) {
            return back()->with('error', 'لا تملك صلاحية نشر الأخبار. احفظ المحتوى كمسودة أو اطلب صلاحية النشر.');
        }

        $post->update([
            ...$validated,
            'updated_by' => $request->user()->id,
        ]);

        return back()->with('success', 'Website content updated.');
    }

    private function storeFeaturedImage(Request $request, array $validated, ?Post $post = null): array
    {
        if (! $request->hasFile('featured_image')) {
            unset($validated['featured_image']);

            if ($post) {
                unset($validated['featured_image_path']);
            }

            return $validated;
        }

        $validated['featured_image_path'] = $this->images->replace(
            $post?->featured_image_path,
            $request->file('featured_image'),
            'website/posts',
        );

        unset($validated['featured_image']);

        return $validated;
    }
}

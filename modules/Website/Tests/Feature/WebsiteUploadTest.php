<?php

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Modules\User\Models\User;
use Modules\Website\Enums\WebsiteContentStatus;
use Modules\Website\Models\Banner;
use Modules\Website\Models\Page;
use Modules\Website\Models\Post;
use Modules\Website\Models\WebsiteSetting;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

function actingAsWebsiteManager(array $permissions): User
{
    app(PermissionRegistrar::class)->forgetCachedPermissions();

    foreach ($permissions as $permission) {
        Permission::findOrCreate($permission, 'web');
    }

    $role = Role::findOrCreate('website_upload_manager', 'web');
    $role->syncPermissions($permissions);

    $user = User::factory()->create();
    $user->assignRole($role);

    return $user;
}

test('website manager can upload a news image', function () {
    Storage::fake('public');

    $user = actingAsWebsiteManager(['website.posts.create', 'website.posts.publish']);

    $response = $this->actingAs($user)->post(route('admin.website.posts.store'), [
        'title' => 'News with image',
        'slug' => 'news-with-image',
        'type' => 'news',
        'status' => WebsiteContentStatus::Published->value,
        'featured_image' => UploadedFile::fake()->image('news.jpg', 1200, 700),
    ]);

    $response->assertSessionHasNoErrors();
    $response->assertRedirect();

    $post = Post::query()->where('slug', 'news-with-image')->firstOrFail();
    expect($post->featured_image_path)->toStartWith('website/posts/');
    Storage::disk('public')->assertExists($post->featured_image_path);
});

test('website manager sees validation errors for invalid news image uploads', function () {
    Storage::fake('public');

    $user = actingAsWebsiteManager(['website.posts.create', 'website.posts.publish']);

    $response = $this->actingAs($user)->post(route('admin.website.posts.store'), [
        'title' => 'News with invalid image',
        'slug' => 'news-with-invalid-image',
        'type' => 'news',
        'status' => WebsiteContentStatus::Published->value,
        'featured_image' => UploadedFile::fake()->create('document.pdf', 128, 'application/pdf'),
    ]);

    $response->assertSessionHasErrors('featured_image');
    expect(Post::query()->where('slug', 'news-with-invalid-image')->exists())->toBeFalse();
});

test('website manager can upload the about page image', function () {
    Storage::fake('public');

    $user = actingAsWebsiteManager(['website.pages.create', 'website.pages.publish']);

    $response = $this->actingAs($user)->post(route('admin.website.pages.store'), [
        'title' => 'عن المعهد',
        'summary' => 'نبذة قصيرة',
        'content' => '<p>محتوى صفحة عن المعهد.</p>',
        'status' => WebsiteContentStatus::Published->value,
        'featured_image' => UploadedFile::fake()->image('about.webp', 1200, 700),
    ]);

    $response->assertSessionHasNoErrors();
    $response->assertRedirect();

    $page = Page::query()->where('slug', 'about')->firstOrFail();
    expect($page->featured_image_path)->toStartWith('website/pages/');
    Storage::disk('public')->assertExists($page->featured_image_path);
});

test('website manager can upload a banner image', function () {
    Storage::fake('public');

    $user = actingAsWebsiteManager(['website.banners.create']);

    $response = $this->actingAs($user)->post(route('admin.website.banners.store'), [
        'title' => 'Homepage slider',
        'subtitle' => 'Welcome banner',
        'is_active' => true,
        'image' => UploadedFile::fake()->image('banner.png', 1400, 700),
    ]);

    $response->assertSessionHasNoErrors();
    $response->assertRedirect();

    $banner = Banner::query()->where('title', 'Homepage slider')->firstOrFail();
    expect($banner->image_path)->toStartWith('website/banners/');
    Storage::disk('public')->assertExists($banner->image_path);
});

test('website manager can upload the settings hero image', function () {
    Storage::fake('public');

    $user = actingAsWebsiteManager(['website.settings.manage']);

    $response = $this->actingAs($user)->post(route('admin.website.settings.update'), [
        '_method' => 'put',
        'site_name' => 'المعهد العالي',
        'hero_image' => UploadedFile::fake()->image('hero.jpg', 1400, 700),
    ]);

    $response->assertSessionHasNoErrors();
    $response->assertRedirect();

    $path = WebsiteSetting::query()
        ->where('key', 'hero_image_path')
        ->value('value');

    expect($path)->toStartWith('website/settings/');
    Storage::disk('public')->assertExists($path);
});

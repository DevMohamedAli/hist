<?php

use Modules\User\Models\User;
use Modules\Website\Enums\WebsiteContentStatus;
use Modules\Website\Models\Page;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

test('public home page loads', function () {
    $this->get(route('home'))->assertOk();
});

test('published pages are visible and drafts are not public', function () {
    $published = Page::factory()->published()->create(['slug' => 'published-page']);
    $draft = Page::factory()->create(['slug' => 'draft-page', 'status' => WebsiteContentStatus::Draft]);

    $this->get(route('website.pages.show', $published->slug))->assertOk();
    $this->get(route('website.pages.show', $draft->slug))->assertNotFound();
});

test('scheduled content is not visible early', function () {
    $page = Page::factory()->create([
        'slug' => 'future-page',
        'status' => WebsiteContentStatus::Published,
        'published_at' => now()->addDay(),
    ]);

    $this->get(route('website.pages.show', $page->slug))->assertNotFound();
});

test('contact form validates required message', function () {
    $this->post(route('website.contact.store'), ['name' => 'Visitor'])
        ->assertSessionHasErrors('message');
});

test('website editor cannot publish posts without publish permission', function () {
    Permission::findOrCreate('website.pages.view', 'web');
    Permission::findOrCreate('website.posts.view', 'web');
    Permission::findOrCreate('website.posts.create', 'web');

    $role = Role::findOrCreate('website_editor', 'web');
    $role->syncPermissions(['website.pages.view', 'website.posts.view', 'website.posts.create']);

    $user = User::factory()->create();
    $user->assignRole($role);

    $this->actingAs($user)
        ->post(route('admin.website.posts.store'), [
            'title' => 'Published news',
            'slug' => 'published-news',
            'type' => 'news',
            'status' => WebsiteContentStatus::Published->value,
        ])
        ->assertRedirect()
        ->assertSessionHas('error');
});

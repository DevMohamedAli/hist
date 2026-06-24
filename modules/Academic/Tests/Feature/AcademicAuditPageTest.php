<?php

use Inertia\Testing\AssertableInertia as Assert;
use Modules\User\Models\User;
use Spatie\Permission\Models\Role;

it('shows the academic audit page to employees', function () {
    $role = Role::findOrCreate('employee', 'web');
    $user = User::factory()->create();
    $user->assignRole($role);

    $this->actingAs($user)
        ->get('/academic/audit')
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Academic/Audit/Index')
            ->has('report.seed_smoke')
            ->has('report.checks')
            ->whereType('report.has_issues', 'boolean'));
});

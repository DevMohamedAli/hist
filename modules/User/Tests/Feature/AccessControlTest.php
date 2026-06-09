<?php

use Inertia\Testing\AssertableInertia as Assert;
use Modules\User\Models\User;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

test('super admin can assign multiple roles and direct permissions to a user', function () {
    $superAdminRole = Role::findOrCreate('super_admin', 'web');
    $teacherRole = Role::findOrCreate('teacher', 'web');
    $employeeRole = Role::findOrCreate('employee', 'web');
    $permission = Permission::findOrCreate('edit grades', 'web');

    $admin = User::factory()->create();
    $admin->assignRole($superAdminRole);

    $user = User::factory()->create();

    $response = $this->actingAs($admin)->put(route('admin.access-control.users.update', $user), [
        'roles' => [$teacherRole->name, $employeeRole->name],
        'direct_permissions' => [$permission->name],
    ]);

    $response->assertRedirect();

    $user->refresh();

    expect($user->hasRole('teacher'))->toBeTrue()
        ->and($user->hasRole('employee'))->toBeTrue()
        ->and($user->hasDirectPermission('edit grades'))->toBeTrue();
});

test('non super admin cannot open access control', function () {
    Role::findOrCreate('employee', 'web');
    $user = User::factory()->create();
    $user->assignRole('employee');

    $this->actingAs($user)
        ->get(route('admin.access-control.index'))
        ->assertForbidden();
});

test('access control page exposes arabic role and permission labels', function () {
    Role::findOrCreate('super_admin', 'web');
    Permission::findOrCreate('manage access control', 'web');

    $admin = User::factory()->create();
    $admin->assignRole('super_admin');

    $this->actingAs($admin)
        ->get(route('admin.access-control.index'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Admin/AccessControl/Index')
            ->where('roleLabels.super_admin', 'المدير العام')
            ->where('permissionLabels.manage access control', 'إدارة الأدوار والصلاحيات')
            ->where('permissionDescriptions.manage access control', 'يسمح بإنشاء الأدوار وتعديل صلاحيات المستخدمين.')
        );
});

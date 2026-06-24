<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $permissions = [
            'access student dashboard',
            'access teacher dashboard',
            'access employee dashboard',
            'manage access control',
            'manage users',
            'manage academic settings',
            'manage courses',
            'manage enrollments',
            'manage imports',
            'manage graduations',
            'manage instructors',
            'view activity logs',
            'view students',
            'edit grades',
            'website.pages.view',
            'website.pages.create',
            'website.pages.update',
            'website.pages.publish',
            'website.pages.archive',
            'website.posts.view',
            'website.posts.create',
            'website.posts.update',
            'website.posts.publish',
            'website.posts.archive',
            'website.banners.view',
            'website.banners.create',
            'website.banners.update',
            'website.faqs.view',
            'website.faqs.create',
            'website.faqs.update',
            'website.settings.manage',
            'website.media.manage',
            'website.contact-submissions.view',
            'correspondence.view-own',
            'correspondence.create',
            'correspondence.send',
            'correspondence.reply',
            'correspondence.forward',
            'correspondence.approve',
            'correspondence.reject',
            'correspondence.return-for-changes',
            'correspondence.complete',
            'correspondence.archive',
            'correspondence.manage-templates',
            'correspondence.manage-categories',
            'correspondence.send-circulars',
            'correspondence.view-confidential',
            'correspondence.view-highly-confidential',
            'correspondence.audit.view',
            'correspondence.admin',
        ];

        foreach ($permissions as $permission) {
            Permission::findOrCreate($permission, 'web');
        }

        $student = Role::findOrCreate('student', 'web');
        $teacher = Role::findOrCreate('teacher', 'web');
        $employee = Role::findOrCreate('employee', 'web');
        $superAdmin = Role::findOrCreate('super_admin', 'web');

        $student->syncPermissions(['access student dashboard']);
        $teacher->syncPermissions(['access teacher dashboard', 'edit grades']);
        $employee->syncPermissions([
            'access employee dashboard',
            'manage access control',
            'view students',
            'manage academic settings',
            'manage courses',
            'manage enrollments',
            'manage imports',
            'manage graduations',
            'manage instructors',
            'view activity logs',
            'edit grades',
            'website.pages.view',
            'website.pages.create',
            'website.pages.update',
            'website.pages.publish',
            'website.contact-submissions.view',
            'website.posts.view',
            'website.posts.create',
            'website.posts.update',
            'website.posts.publish',
            'website.banners.view',
            'website.banners.create',
            'website.banners.update',
            'website.faqs.view',
            'website.faqs.create',
            'website.faqs.update',
            'website.settings.manage',
            'correspondence.view-own',
            'correspondence.create',
            'correspondence.send',
            'correspondence.reply',
            'correspondence.complete',
            'correspondence.archive',
        ]);
        $superAdmin->syncPermissions(Permission::all());
    }
}

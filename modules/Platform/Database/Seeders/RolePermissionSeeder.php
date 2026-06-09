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
            'manage instructors',
            'view activity logs',
            'view students',
            'edit grades',
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
            'manage instructors',
            'view activity logs',
            'edit grades',
        ]);
        $superAdmin->syncPermissions(Permission::all());
    }
}

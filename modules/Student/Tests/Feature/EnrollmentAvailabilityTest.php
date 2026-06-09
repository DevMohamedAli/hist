<?php

use Modules\Academic\Models\Department;
use Modules\Academic\Models\Specialization;
use Modules\Student\Models\Student;
use Modules\User\Models\User;
use Spatie\Permission\Models\Role;

it('does not allow opening the enrollment page outside the registration period', function () {
    $user = User::factory()->create();
    $role = Role::create(['name' => 'super_admin', 'guard_name' => 'web']);
    $user->assignRole($role);
    $department = Department::create(['name' => 'قسم التسجيل', 'code' => 'REG']);
    $specialization = Specialization::create([
        'department_id' => $department->id,
        'name' => 'تخصص التسجيل',
        'code' => 'REG-SP',
        'semesters_count' => 6,
    ]);
    $student = Student::create([
        'registration_number' => '126261002',
        'full_name' => 'طالب تسجيل',
        'national_id' => '126260000002',
        'gender' => 'Male',
        'nationality' => 'ليبي',
        'birth_date' => '2005-01-01',
        'admission_date' => '2026-01-01',
        'current_specialization_id' => $specialization->id,
        'current_semester_level' => 1,
        'status' => 'Active',
    ]);

    $response = $this->actingAs($user)->get(route('students.enroll.create', $student));

    $response->assertRedirect(route('students.show', $student));
    $response->assertSessionHasErrors('enrollment');
});

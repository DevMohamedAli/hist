<?php

use Modules\Academic\Models\Department;
use Modules\Academic\Models\Specialization;
use Modules\Student\Models\DepartmentTransfer;
use Modules\Student\Models\Student;
use Modules\User\Models\User;
use Spatie\Permission\Models\Role;

function transferRuleFixture(): array
{
    $user = User::factory()->create();
    $role = Role::create(['name' => 'super_admin', 'guard_name' => 'web']);
    $user->assignRole($role);

    $department = Department::create(['name' => 'قسم الانتقال', 'code' => 'TRN']);
    $from = Specialization::create([
        'department_id' => $department->id,
        'name' => 'التخصص الأول',
        'code' => 'TRN-A',
        'semesters_count' => 6,
    ]);
    $to = Specialization::create([
        'department_id' => $department->id,
        'name' => 'التخصص الثاني',
        'code' => 'TRN-B',
        'semesters_count' => 6,
    ]);
    $third = Specialization::create([
        'department_id' => $department->id,
        'name' => 'التخصص الثالث',
        'code' => 'TRN-C',
        'semesters_count' => 6,
    ]);
    $student = Student::create([
        'registration_number' => '126261003',
        'full_name' => 'طالب انتقال',
        'national_id' => '126260000003',
        'gender' => 'Male',
        'nationality' => 'ليبي',
        'birth_date' => '2005-01-01',
        'admission_date' => '2026-01-01',
        'current_specialization_id' => $to->id,
        'current_semester_level' => 1,
        'status' => 'Active',
    ]);

    return compact('user', 'student', 'from', 'to', 'third');
}

it('rejects a second specialization transfer for the same student', function () {
    $fixture = transferRuleFixture();

    DepartmentTransfer::create([
        'student_id' => $fixture['student']->id,
        'from_specialization_id' => $fixture['from']->id,
        'to_specialization_id' => $fixture['to']->id,
        'transfer_date' => '2026-02-10',
        'approval_reference' => 'قرار 1',
    ]);

    $response = $this->actingAs($fixture['user'])->post(route('students.transfer', $fixture['student']), [
        'to_specialization_id' => $fixture['third']->id,
        'transfer_date' => '2026-03-01',
        'approval_reference' => 'قرار 2',
    ]);

    $response->assertSessionHasErrors('transfer');
    expect(DepartmentTransfer::where('student_id', $fixture['student']->id)->count())->toBe(1);
});

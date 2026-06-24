<?php

use Modules\Academic\Models\AcademicSemester;
use Modules\Academic\Models\Course;
use Modules\Academic\Models\CourseClass;
use Modules\Academic\Models\Department;
use Modules\Academic\Models\Specialization;
use Modules\Academic\Models\StudyGroup;
use Modules\Platform\Models\Setting;
use Modules\Staff\Models\Instructor;
use Modules\Student\Models\CourseEnrollment;
use Modules\Student\Models\Student;
use Modules\User\Models\User;
use Spatie\Permission\Models\Role;

function workflowUser(string $roleName): User
{
    $role = Role::findOrCreate($roleName, 'web');
    $user = User::factory()->create();
    $user->assignRole($role);

    return $user;
}

function makeWorkflowSpecialization(): Specialization
{
    $suffix = substr(str_replace('.', '', uniqid('', true)), 0, 6);
    $department = Department::create([
        'name' => 'قسم '.$suffix,
        'code' => 'D'.$suffix,
    ]);

    return Specialization::create([
        'department_id' => $department->id,
        'name' => 'تخصص '.$suffix,
        'code' => 'S'.$suffix,
        'semesters_count' => 6,
    ]);
}

it('assigns a newly registered active student to an open first-semester study group automatically', function () {
    Setting::updateOrCreate(['key' => 'admin_code'], ['value' => '2']);
    Setting::updateOrCreate(['key' => 'institute_code'], ['value' => '10']);

    $user = workflowUser('super_admin');
    $specialization = makeWorkflowSpecialization();
    $semester = AcademicSemester::create([
        'code' => 'REG-OPEN',
        'season' => 'Fall',
        'year' => 2026,
        'start_date' => now()->subDay(),
        'end_date' => now()->addMonths(4),
        'registration_start' => now()->subDay(),
        'registration_end' => now()->addDay(),
        'final_exams_start' => now()->addMonths(3),
    ]);

    $this->actingAs($user)->post('/students', [
        'full_name' => 'طالب مسار جديد',
        'national_id' => '126260000111',
        'gender' => 'Male',
        'nationality' => 'ليبي',
        'birth_date' => '2005-01-01',
        'mobile' => '0910000000',
        'admission_date' => now()->toDateString(),
        'current_specialization_id' => $specialization->id,
        'qualification_mode' => 'none',
        'account_mode' => 'none',
    ])->assertRedirect();

    $student = Student::query()->latest('id')->firstOrFail();

    expect($student->current_study_group_id)->not->toBeNull();

    $group = StudyGroup::findOrFail($student->current_study_group_id);

    expect((int) $group->academic_semester_id)->toBe($semester->id)
        ->and((int) $group->specialization_id)->toBe($specialization->id)
        ->and((int) $group->semester_level)->toBe(1)
        ->and($group->group_name)->toBe('A');
});

it('blocks teachers from opening grade entry before the finals window starts', function () {
    $user = workflowUser('teacher');
    $specialization = makeWorkflowSpecialization();
    $semester = AcademicSemester::create([
        'code' => 'GRADE-LOCK',
        'season' => 'Fall',
        'year' => 2026,
        'start_date' => now()->subMonth(),
        'end_date' => now()->addMonth(),
        'registration_start' => now()->subMonths(2),
        'registration_end' => now()->subMonth(),
        'final_exams_start' => now()->addWeek(),
    ]);
    $group = StudyGroup::create([
        'specialization_id' => $specialization->id,
        'academic_semester_id' => $semester->id,
        'semester_level' => 1,
        'group_name' => 'A',
        'capacity' => 30,
    ]);
    $instructor = Instructor::create([
        'name' => 'معلم الرصد',
        'email' => 'grade-lock@example.test',
        'user_id' => $user->id,
    ]);
    $course = Course::create([
        'code' => 'CRS-LOCK',
        'name' => 'مقرر مغلق',
        'units' => 3,
        'has_practical' => false,
    ]);
    $specialization->courses()->attach($course->id, ['semester_level' => 1]);
    $class = CourseClass::create([
        'course_id' => $course->id,
        'semester_id' => $semester->id,
        'study_group_id' => $group->id,
        'instructor_id' => $instructor->id,
        'group_name' => 'A',
    ]);
    $student = Student::create([
        'registration_number' => '126261234',
        'full_name' => 'طالب رصد',
        'national_id' => '126260000222',
        'gender' => 'Male',
        'nationality' => 'ليبي',
        'birth_date' => '2000-01-01',
        'admission_date' => '2024-01-01',
        'current_specialization_id' => $specialization->id,
        'current_study_group_id' => $group->id,
        'current_semester_level' => 1,
        'status' => 'Active',
    ]);
    CourseEnrollment::create([
        'student_id' => $student->id,
        'study_group_id' => $group->id,
        'class_id' => $class->id,
        'course_id' => $course->id,
        'status' => 'Pending',
    ]);

    $this->actingAs($user)
        ->get("/grades/classes/{$class->id}")
        ->assertRedirect(route('grades.index'))
        ->assertSessionHasErrors('grades');
});

it('allows super admins to override the closed grade-entry window', function () {
    $user = workflowUser('super_admin');
    $specialization = makeWorkflowSpecialization();
    $semester = AcademicSemester::create([
        'code' => 'GRADE-ADMIN',
        'season' => 'Fall',
        'year' => 2026,
        'start_date' => now()->subMonth(),
        'end_date' => now()->addMonth(),
        'registration_start' => now()->subMonths(2),
        'registration_end' => now()->subMonth(),
        'final_exams_start' => now()->addWeek(),
    ]);
    $group = StudyGroup::create([
        'specialization_id' => $specialization->id,
        'academic_semester_id' => $semester->id,
        'semester_level' => 1,
        'group_name' => 'A',
        'capacity' => 30,
    ]);
    $instructor = Instructor::create([
        'name' => 'معلم الإدارة',
        'email' => 'grade-admin@example.test',
    ]);
    $course = Course::create([
        'code' => 'CRS-ADMIN',
        'name' => 'مقرر إداري',
        'units' => 3,
        'has_practical' => false,
    ]);
    $specialization->courses()->attach($course->id, ['semester_level' => 1]);
    $class = CourseClass::create([
        'course_id' => $course->id,
        'semester_id' => $semester->id,
        'study_group_id' => $group->id,
        'instructor_id' => $instructor->id,
        'group_name' => 'A',
    ]);

    $this->actingAs($user)
        ->get("/grades/classes/{$class->id}")
        ->assertOk();
});

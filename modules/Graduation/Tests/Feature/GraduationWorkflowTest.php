<?php

use Modules\Academic\Models\AcademicSemester;
use Modules\Academic\Models\Course;
use Modules\Academic\Models\CourseClass;
use Modules\Academic\Models\Department;
use Modules\Academic\Models\Specialization;
use Modules\Academic\Models\StudyGroup;
use Modules\Graduation\Models\GraduationRecord;
use Modules\Staff\Models\Instructor;
use Modules\Student\Models\CourseEnrollment;
use Modules\User\Models\User;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

function graduationTestUser(): User
{
    Permission::findOrCreate('manage graduations', 'web');
    $role = Role::findOrCreate('employee', 'web');
    $role->givePermissionTo('manage graduations');
    $user = User::factory()->create();
    $user->assignRole($role);

    return $user;
}

function makeGraduationWorkflowStudent(array $totals = [70, 80], bool $attachSecondCourse = true): array
{
    $suffix = uniqid();
    $department = Department::create(['name' => 'القسم الطبي '.$suffix, 'code' => uniqid('DEP')]);
    $specialization = Specialization::create([
        'department_id' => $department->id,
        'name' => 'الصيدلة '.$suffix,
        'code' => uniqid('SPC'),
        'semesters_count' => 2,
    ]);
    $semester = AcademicSemester::create(['code' => uniqid('FALL'), 'season' => 'Fall', 'year' => 2026]);
    $studyGroup = StudyGroup::create([
        'specialization_id' => $specialization->id,
        'academic_semester_id' => $semester->id,
        'semester_level' => 1,
        'group_name' => 'أ',
        'capacity' => 30,
    ]);
    $instructor = Instructor::create(['name' => 'محاضر الاختبار', 'email' => uniqid('teacher').'@example.test']);
    $student = \Modules\Student\Models\Student::create([
        'registration_number' => uniqid('REG'),
        'full_name' => 'طالب اختبار',
        'national_id' => str_pad((string) random_int(1, 999999999999), 12, '0', STR_PAD_LEFT),
        'gender' => 'Male',
        'nationality' => 'ليبي',
        'birth_date' => '2000-01-01',
        'admission_date' => '2024-01-01',
        'current_specialization_id' => $specialization->id,
        'current_semester_level' => 2,
        'status' => 'Active',
    ]);

    $courses = collect([1, 2])->map(function (int $index) use ($specialization) {
        $course = Course::create([
            'code' => uniqid('CRS'),
            'name' => 'مقرر '.$index,
            'units' => 3,
            'has_practical' => false,
        ]);
        $specialization->courses()->attach($course->id, ['semester_level' => $index]);

        return $course;
    });

    foreach ($courses as $index => $course) {
        if ($index === 1 && ! $attachSecondCourse) {
            continue;
        }

        $class = CourseClass::create([
            'course_id' => $course->id,
            'semester_id' => $semester->id,
            'study_group_id' => $studyGroup->id,
            'instructor_id' => $instructor->id,
            'group_name' => 'أ',
        ]);

        CourseEnrollment::create([
            'student_id' => $student->id,
            'study_group_id' => $studyGroup->id,
            'class_id' => $class->id,
            'course_id' => $course->id,
            'raw_semester_work' => 30,
            'raw_final_exam' => max(30, ($totals[$index] ?? 70) - 30),
            'total_mark' => $totals[$index] ?? 70,
            'grade_evaluation' => 'مقبول',
            'status' => ($totals[$index] ?? 70) >= 50 ? 'Passed' : 'Failed',
        ]);
    }

    return [$student, $courses];
}

it('shows eligible students in the graduation list', function () {
    [$student] = makeGraduationWorkflowStudent([70, 80]);
    $user = graduationTestUser();

    $this->actingAs($user)
        ->get('/graduations?status=eligible')
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('Graduation/Index')
            ->where('students.data.0.id', $student->id)
        );
});

it('blocks approval for ineligible students', function () {
    [$student] = makeGraduationWorkflowStudent([70], false);
    $user = graduationTestUser();

    $this->actingAs($user)
        ->post("/graduations/{$student->id}/approve")
        ->assertSessionHasErrors('student');

    expect(GraduationRecord::count())->toBe(0);
});

it('approves graduation, increments certificate numbers, and marks students graduated', function () {
    [$first] = makeGraduationWorkflowStudent([70, 80]);
    [$second] = makeGraduationWorkflowStudent([75, 85]);
    $user = graduationTestUser();

    $this->actingAs($user)->post("/graduations/{$first->id}/approve")->assertRedirect();
    $this->actingAs($user)->post("/graduations/{$second->id}/approve")->assertRedirect();

    $records = GraduationRecord::query()->orderBy('certificate_number')->get();

    expect($records)->toHaveCount(2)
        ->and($records[0]->certificate_number)->toBe('GR-'.now()->format('Y').'-0001')
        ->and($records[1]->certificate_number)->toBe('GR-'.now()->format('Y').'-0002')
        ->and($first->refresh()->status)->toBe('Graduated')
        ->and($second->refresh()->status)->toBe('Graduated');
});

it('returns certificate and study report pdf responses', function () {
    [$student] = makeGraduationWorkflowStudent([70, 80]);
    $user = graduationTestUser();
    $this->actingAs($user)->post("/graduations/{$student->id}/approve");
    $record = GraduationRecord::firstOrFail();

    $this->actingAs($user)
        ->get("/graduation-records/{$record->id}/certificate")
        ->assertOk()
        ->assertHeader('content-type', 'application/pdf');

    $this->actingAs($user)
        ->get("/graduation-records/{$record->id}/study-report")
        ->assertOk()
        ->assertHeader('content-type', 'application/pdf');
});

it('keeps approval counts stable across approved pagination pages', function () {
    $students = collect(range(1, 21))->map(fn () => makeGraduationWorkflowStudent([70, 80])[0]);
    $user = graduationTestUser();

    foreach ($students as $student) {
        $this->actingAs($user)->post("/graduations/{$student->id}/approve")->assertRedirect();
    }

    $this->actingAs($user)
        ->get('/graduations?status=approved&page=2')
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('Graduation/Index')
            ->where('counts.approved', 21)
            ->where('counts.eligible', 0)
            ->where('counts.blocked', 0)
            ->where('students.total', 21)
            ->has('students.data', 1)
        );
});

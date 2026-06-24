<?php

use Illuminate\Support\Facades\Hash;
use Modules\Academic\Models\AcademicSemester;
use Modules\Academic\Models\Course;
use Modules\Academic\Models\CourseClass;
use Modules\Academic\Models\Department;
use Modules\Academic\Models\Specialization;
use Modules\Academic\Models\StudyGroup;
use Modules\Staff\Models\Instructor;
use Modules\Student\Models\Student;
use Modules\User\Models\User;
use Spatie\Permission\Models\Role;

function makeGraduatedStudentActionFixture(): array
{
    $suffix = substr(str_replace('.', '', uniqid('', true)), 0, 8);

    $department = Department::create([
        'name' => 'Graduation Lock Department '.$suffix,
        'code' => 'GLD'.$suffix,
    ]);

    $specialization = Specialization::create([
        'department_id' => $department->id,
        'name' => 'Graduation Lock Specialization '.$suffix,
        'code' => 'GLS'.$suffix,
        'semesters_count' => 6,
    ]);

    $semester = AcademicSemester::create([
        'code' => 'GL'.$suffix,
        'season' => 'Spring',
        'year' => 2026,
        'start_date' => now()->subMonth(),
        'end_date' => now()->addMonth(),
        'registration_start' => now()->subMonth(),
        'registration_end' => now()->addWeek(),
    ]);

    $studyGroup = StudyGroup::create([
        'specialization_id' => $specialization->id,
        'academic_semester_id' => $semester->id,
        'semester_level' => 6,
        'group_name' => 'A',
        'capacity' => 30,
    ]);

    $instructor = Instructor::create([
        'department_id' => $department->id,
        'name' => 'Graduation Lock Instructor '.$suffix,
        'email' => 'lock'.$suffix.'@example.test',
    ]);

    $course = Course::create([
        'code' => 'GLC'.$suffix,
        'name' => 'Graduation Lock Course '.$suffix,
        'units' => 3,
        'has_practical' => false,
    ]);

    $specialization->courses()->attach($course->id, ['semester_level' => 6]);

    $courseClass = CourseClass::create([
        'course_id' => $course->id,
        'semester_id' => $semester->id,
        'study_group_id' => $studyGroup->id,
        'instructor_id' => $instructor->id,
        'group_name' => 'A',
    ]);

    $user = User::factory()->create([
        'password' => Hash::make('password'),
    ]);
    $user->assignRole(Role::findOrCreate('employee', 'web'));

    $student = Student::create([
        'registration_number' => substr('GL'.$suffix, 0, 9),
        'full_name' => 'Graduated Lock Student '.$suffix,
        'national_id' => str_pad((string) random_int(1, 999999999999), 12, '0', STR_PAD_LEFT),
        'gender' => 'Male',
        'nationality' => 'Libyan',
        'birth_date' => '2000-01-01',
        'admission_date' => '2024-01-01',
        'current_specialization_id' => $specialization->id,
        'current_semester_level' => 6,
        'status' => 'Graduated',
    ]);

    return compact('department', 'specialization', 'semester', 'studyGroup', 'course', 'courseClass', 'student', 'user');
}

test('graduated student page locks academic actions in props', function () {
    $fixture = makeGraduatedStudentActionFixture();

    $this->actingAs($fixture['user'])
        ->get("/students/{$fixture['student']->id}")
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('Student/Show')
            ->where('student.status', 'Graduated')
            ->where('enrollmentAvailability.is_open', false)
            ->where('enrollmentAvailability.message', 'الطالب متخرج بالفعل، ولا يمكن تسجيل أو تنزيل المقررات له.')
            ->where('transferEligibility.can_transfer', false)
            ->where('transferEligibility.message', 'الطالب متخرج بالفعل، ولا يمكن انتقال التخصص له.')
        );
});

test('graduated student cannot suspend transfer or enroll', function () {
    $fixture = makeGraduatedStudentActionFixture();
    $studentId = $fixture['student']->id;

    $this->actingAs($fixture['user'])
        ->post("/students/{$studentId}/suspend", [
            'semester_id' => $fixture['semester']->id,
            'approval_date' => now()->toDateString(),
            'suspension_reason' => 'test',
        ])
        ->assertSessionHasErrors('status');

    $this->actingAs($fixture['user'])
        ->post("/students/{$studentId}/transfer", [
            'to_specialization_id' => $fixture['specialization']->id,
            'transfer_date' => now()->toDateString(),
            'approval_reference' => 'REF-1',
        ])
        ->assertSessionHasErrors('transfer');

    $this->actingAs($fixture['user'])
        ->post("/students/{$studentId}/enroll", [
            'study_group_id' => $fixture['studyGroup']->id,
            'selected_course_ids' => [$fixture['course']->id],
        ])
        ->assertSessionHasErrors('enrollment');
});

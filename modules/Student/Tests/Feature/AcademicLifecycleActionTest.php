<?php

use Modules\Academic\Models\AcademicSemester;
use Modules\Academic\Models\Course;
use Modules\Academic\Models\CourseClass;
use Modules\Academic\Models\Department;
use Modules\Academic\Models\Specialization;
use Modules\Academic\Models\StudyGroup;
use Modules\Staff\Models\Instructor;
use Modules\Student\Actions\CheckGraduationAction;
use Modules\Student\Actions\PromoteStudentAction;
use Modules\Student\Actions\RecordCourseGrades;
use Modules\Student\Models\CourseEnrollment;
use Modules\Student\Models\Student;
use Modules\Student\Models\StudentSemesterSummary;

function makeAcademicLifecycleFixture(string $enrollmentStatus = 'Pending'): array
{
    $suffix = substr(str_replace('.', '', uniqid('', true)), 0, 8);
    $department = Department::create([
        'name' => 'Lifecycle Department '.$suffix,
        'code' => 'D'.$suffix,
    ]);
    $specialization = Specialization::create([
        'department_id' => $department->id,
        'name' => 'Lifecycle Specialization '.$suffix,
        'code' => 'S'.$suffix,
        'semesters_count' => 1,
    ]);
    $semester = AcademicSemester::create([
        'code' => 'LIFE'.$suffix,
        'season' => 'Fall',
        'year' => 2026,
        'start_date' => now()->subMonths(4),
        'end_date' => now()->subMonth(),
        'registration_start' => now()->subMonths(5),
        'registration_end' => now()->subMonths(4),
    ]);
    $studyGroup = StudyGroup::create([
        'specialization_id' => $specialization->id,
        'academic_semester_id' => $semester->id,
        'semester_level' => 1,
        'group_name' => 'A',
        'capacity' => 30,
    ]);
    $instructor = Instructor::create([
        'name' => 'Lifecycle Instructor '.$suffix,
        'email' => 'teacher'.$suffix.'@example.test',
    ]);
    $course = Course::create([
        'code' => 'C'.$suffix,
        'name' => 'Lifecycle Course '.$suffix,
        'units' => 3,
        'has_practical' => false,
    ]);
    $specialization->courses()->attach($course->id, ['semester_level' => 1]);
    $class = CourseClass::create([
        'course_id' => $course->id,
        'semester_id' => $semester->id,
        'study_group_id' => $studyGroup->id,
        'instructor_id' => $instructor->id,
        'group_name' => 'A',
    ]);
    $student = Student::create([
        'registration_number' => substr('REG'.$suffix, 0, 9),
        'full_name' => 'Lifecycle Student '.$suffix,
        'national_id' => str_pad((string) random_int(1, 999999999999), 12, '0', STR_PAD_LEFT),
        'gender' => 'Male',
        'nationality' => 'Libyan',
        'birth_date' => '2000-01-01',
        'admission_date' => '2024-01-01',
        'current_specialization_id' => $specialization->id,
        'current_semester_level' => 1,
        'status' => 'Active',
    ]);
    $enrollment = CourseEnrollment::create([
        'student_id' => $student->id,
        'study_group_id' => $studyGroup->id,
        'class_id' => $class->id,
        'course_id' => $course->id,
        'raw_semester_work' => $enrollmentStatus === 'Pending' ? 0 : 35,
        'raw_final_exam' => $enrollmentStatus === 'Pending' ? 0 : 35,
        'total_mark' => $enrollmentStatus === 'Pending' ? 0 : 70,
        'grade_evaluation' => $enrollmentStatus === 'Pending' ? null : 'Good',
        'status' => $enrollmentStatus,
    ]);

    return compact('student', 'semester', 'enrollment', 'course');
}

it('recalculates the semester summary after recording grades', function () {
    $fixture = makeAcademicLifecycleFixture();

    app(RecordCourseGrades::class)->execute($fixture['enrollment']->id, 35, 35);

    $summary = StudentSemesterSummary::where('student_id', $fixture['student']->id)
        ->where('semester_id', $fixture['semester']->id)
        ->first();

    expect($summary)->not->toBeNull()
        ->and((float) $summary->semester_gpa)->toBe(70.0)
        ->and((int) $summary->total_registered_units)->toBe(3);
});

it('blocks promotion while grades are pending', function () {
    $fixture = makeAcademicLifecycleFixture();

    $result = app(PromoteStudentAction::class)->execute($fixture['student'], $fixture['semester']->id);

    expect($result['promoted'])->toBeFalse()
        ->and($result['pending_count'])->toBe(1)
        ->and($fixture['student']->fresh()->current_semester_level)->toBe(1);
});

it('promotes a completed semester only once', function () {
    $fixture = makeAcademicLifecycleFixture('Passed');

    $first = app(PromoteStudentAction::class)->execute($fixture['student'], $fixture['semester']->id);
    $second = app(PromoteStudentAction::class)->execute($fixture['student']->fresh(), $fixture['semester']->id);

    expect($first['promoted'])->toBeTrue()
        ->and($first['new_level'])->toBe(2)
        ->and($second['promoted'])->toBeFalse()
        ->and($fixture['student']->fresh()->current_semester_level)->toBe(2);
});

it('checks graduation eligibility without directly graduating the student', function () {
    $fixture = makeAcademicLifecycleFixture('Passed');

    $result = app(CheckGraduationAction::class)->execute($fixture['student']);

    expect($result['eligible'])->toBeTrue()
        ->and($result['is_graduated'])->toBeFalse()
        ->and($fixture['student']->fresh()->status)->toBe('Active');
});

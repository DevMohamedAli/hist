<?php

use Modules\Academic\Models\AcademicSemester;
use Modules\Academic\Models\Course;
use Modules\Academic\Models\CourseClass;
use Modules\Academic\Models\Department;
use Modules\Academic\Models\Specialization;
use Modules\Academic\Models\StudyGroup;
use Modules\Graduation\Actions\GraduationEligibilityAction;
use Modules\Staff\Models\Instructor;
use Modules\Student\Models\CourseEnrollment;
use Modules\Student\Models\Student;

function makeGraduationEligibilityStudent(array $totals = [70, 80], bool $attachSecondCourse = true): array
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
    $student = Student::create([
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

it('passes when all curriculum courses are passed and cgpa is at least 55', function () {
    [$student] = makeGraduationEligibilityStudent([70, 80]);

    $result = app(GraduationEligibilityAction::class)->execute($student);

    expect($result['eligible'])->toBeTrue()
        ->and($result['cgpa'])->toBe(75.0)
        ->and($result['total_units'])->toBe(6)
        ->and($result['missing_courses'])->toBe([]);
});

it('fails when a curriculum course is missing', function () {
    [$student] = makeGraduationEligibilityStudent([70], false);

    $result = app(GraduationEligibilityAction::class)->execute($student);

    expect($result['eligible'])->toBeFalse()
        ->and($result['missing_courses'])->toHaveCount(1);
});

it('fails when cgpa is below the graduation minimum', function () {
    [$student] = makeGraduationEligibilityStudent([50, 50]);

    $result = app(GraduationEligibilityAction::class)->execute($student);

    expect($result['eligible'])->toBeFalse()
        ->and($result['cgpa'])->toBe(50.0);
});

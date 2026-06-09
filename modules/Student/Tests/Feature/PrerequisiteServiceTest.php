<?php

use Modules\Academic\Models\AcademicSemester;
use Modules\Academic\Models\Course;
use Modules\Academic\Models\CourseClass;
use Modules\Academic\Models\Department;
use Modules\Academic\Models\Specialization;
use Modules\Academic\Models\StudyGroup;
use Modules\Staff\Models\Instructor;
use Modules\Student\Models\CourseEnrollment;
use Modules\Student\Models\Student;
use Modules\Student\Services\PrerequisiteService;

function prerequisiteFixture(): array
{
    $department = Department::create(['name' => 'قسم الاختبار', 'code' => 'TST']);
    $specialization = Specialization::create([
        'department_id' => $department->id,
        'name' => 'تخصص الاختبار',
        'code' => 'TST-SP',
        'semesters_count' => 6,
    ]);
    $semester = AcademicSemester::create([
        'code' => 'TST-2026',
        'season' => 'Spring',
        'year' => 2026,
    ]);
    $studyGroup = StudyGroup::create([
        'specialization_id' => $specialization->id,
        'academic_semester_id' => $semester->id,
        'semester_level' => 1,
        'group_name' => 'أ',
    ]);
    $instructor = Instructor::create([
        'department_id' => $department->id,
        'name' => 'محاضر اختبار',
        'academic_rank' => 'محاضر',
        'status' => 'Active',
    ]);
    $student = Student::create([
        'registration_number' => '126261001',
        'full_name' => 'طالب اختبار',
        'national_id' => '126260000001',
        'gender' => 'Male',
        'nationality' => 'ليبي',
        'birth_date' => '2005-01-01',
        'admission_date' => '2026-01-01',
        'current_specialization_id' => $specialization->id,
        'current_semester_level' => 1,
        'status' => 'Active',
    ]);
    $prerequisite = Course::create([
        'code' => 'PRE101',
        'name' => 'متطلب سابق',
        'units' => 3,
        'has_practical' => false,
    ]);
    $course = Course::create([
        'code' => 'ADV201',
        'name' => 'مقرر متقدم',
        'units' => 3,
        'has_practical' => false,
    ]);
    $course->prerequisites()->sync([$prerequisite->id]);

    $class = CourseClass::create([
        'course_id' => $prerequisite->id,
        'semester_id' => $semester->id,
        'study_group_id' => $studyGroup->id,
        'instructor_id' => $instructor->id,
        'group_name' => 'أ',
    ]);

    return compact('student', 'prerequisite', 'course', 'studyGroup', 'class');
}

it('blocks enrollment when the prerequisite course is not passed', function () {
    $fixture = prerequisiteFixture();

    CourseEnrollment::create([
        'student_id' => $fixture['student']->id,
        'study_group_id' => $fixture['studyGroup']->id,
        'class_id' => $fixture['class']->id,
        'course_id' => $fixture['prerequisite']->id,
        'status' => 'Failed',
    ]);

    $service = app(PrerequisiteService::class);

    expect($service->canEnroll($fixture['student'], $fixture['course']))->toBeFalse()
        ->and($service->missingPrerequisites($fixture['student'], $fixture['course'])->pluck('code')->all())
        ->toBe(['PRE101']);
});

it('allows enrollment after the prerequisite course is passed', function () {
    $fixture = prerequisiteFixture();

    CourseEnrollment::create([
        'student_id' => $fixture['student']->id,
        'study_group_id' => $fixture['studyGroup']->id,
        'class_id' => $fixture['class']->id,
        'course_id' => $fixture['prerequisite']->id,
        'status' => 'Passed',
    ]);

    expect(app(PrerequisiteService::class)->canEnroll($fixture['student'], $fixture['course']))->toBeTrue();
});

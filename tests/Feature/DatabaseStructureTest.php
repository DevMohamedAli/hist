<?php

use Illuminate\Database\QueryException;
use Modules\Academic\Models\AcademicSemester;
use Modules\Academic\Models\Course;
use Modules\Academic\Models\CourseClass;
use Modules\Academic\Models\Department;
use Modules\Academic\Models\Specialization;
use Modules\Academic\Models\StudyGroup;
use Modules\Staff\Models\Instructor;
use Modules\Student\Models\CourseEnrollment;
use Modules\Student\Models\Student;
use Modules\User\Models\User;

function makeDatabaseStructureFixture(): array
{
    static $year = 1901;

    $suffix = substr(str_replace('.', '', uniqid('', true)), 0, 8);
    $department = Department::create([
        'name' => 'DB Department '.$suffix,
        'code' => 'DBD'.$suffix,
    ]);
    $specialization = Specialization::create([
        'department_id' => $department->id,
        'name' => 'DB Specialization '.$suffix,
        'code' => 'DBS'.$suffix,
        'semesters_count' => 6,
    ]);
    $semester = AcademicSemester::create([
        'code' => 'DB'.$suffix,
        'season' => 'Fall',
        'year' => $year++,
    ]);
    $studyGroup = StudyGroup::create([
        'specialization_id' => $specialization->id,
        'academic_semester_id' => $semester->id,
        'semester_level' => 1,
        'group_name' => 'A',
        'capacity' => 30,
    ]);
    $instructor = Instructor::create([
        'department_id' => $department->id,
        'name' => 'DB Instructor '.$suffix,
        'email' => 'db'.$suffix.'@example.test',
    ]);
    $course = Course::create([
        'code' => 'DBC'.$suffix,
        'name' => 'DB Course '.$suffix,
        'units' => 3,
        'has_practical' => false,
    ]);
    $class = CourseClass::create([
        'course_id' => $course->id,
        'semester_id' => $semester->id,
        'study_group_id' => $studyGroup->id,
        'instructor_id' => $instructor->id,
        'group_name' => 'A',
    ]);
    $student = Student::create([
        'registration_number' => substr('DB'.$suffix, 0, 9),
        'full_name' => 'DB Student '.$suffix,
        'national_id' => str_pad((string) random_int(1, 999999999999), 12, '0', STR_PAD_LEFT),
        'gender' => 'Male',
        'nationality' => 'Libyan',
        'birth_date' => '2000-01-01',
        'admission_date' => '2024-01-01',
        'current_specialization_id' => $specialization->id,
        'current_semester_level' => 1,
        'status' => 'Active',
    ]);

    return compact('department', 'specialization', 'semester', 'studyGroup', 'instructor', 'course', 'class', 'student');
}

it('prevents duplicate study groups for the same specialization semester and level', function () {
    $fixture = makeDatabaseStructureFixture();

    expect(fn () => StudyGroup::create([
        'specialization_id' => $fixture['specialization']->id,
        'academic_semester_id' => $fixture['semester']->id,
        'semester_level' => 1,
        'group_name' => 'A',
        'capacity' => 30,
    ]))->toThrow(QueryException::class);
});

it('prevents duplicate course classes for the same course semester and study group', function () {
    $fixture = makeDatabaseStructureFixture();

    expect(fn () => CourseClass::create([
        'course_id' => $fixture['course']->id,
        'semester_id' => $fixture['semester']->id,
        'study_group_id' => $fixture['studyGroup']->id,
        'instructor_id' => $fixture['instructor']->id,
        'group_name' => 'A',
    ]))->toThrow(QueryException::class);
});

it('prevents duplicate course enrollments for the same student course and study group', function () {
    $fixture = makeDatabaseStructureFixture();

    CourseEnrollment::create([
        'student_id' => $fixture['student']->id,
        'study_group_id' => $fixture['studyGroup']->id,
        'class_id' => $fixture['class']->id,
        'course_id' => $fixture['course']->id,
        'status' => 'Pending',
    ]);

    expect(fn () => CourseEnrollment::create([
        'student_id' => $fixture['student']->id,
        'study_group_id' => $fixture['studyGroup']->id,
        'class_id' => $fixture['class']->id,
        'course_id' => $fixture['course']->id,
        'status' => 'Pending',
    ]))->toThrow(QueryException::class);
});

it('prevents duplicate user import mapping template names for the same type', function () {
    $user = User::factory()->create();

    DB::table('import_mapping_templates')->insert([
        'user_id' => $user->id,
        'type' => 'students',
        'name' => 'Default',
        'mapping' => json_encode(['name' => 'full_name']),
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    expect(fn () => DB::table('import_mapping_templates')->insert([
        'user_id' => $user->id,
        'type' => 'students',
        'name' => 'Default',
        'mapping' => json_encode(['name' => 'full_name']),
        'created_at' => now(),
        'updated_at' => now(),
    ]))->toThrow(QueryException::class);
});

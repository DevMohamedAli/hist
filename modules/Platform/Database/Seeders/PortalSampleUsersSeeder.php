<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
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

class PortalSampleUsersSeeder extends Seeder
{
    public function run(): void
    {
        $this->seedSuperAdmin();
        $this->seedPortalUsers();
    }

    private function seedSuperAdmin(): User
    {
        $user = User::updateOrCreate(
            ['email' => 'admin@hist.edu.ly'],
            [
                'name' => 'مدير النظام العام',
                'password' => Hash::make('password123'),
                'email_verified_at' => now(),
            ]
        );

        $user->assignRole('super_admin');

        return $user;
    }

    private function seedPortalUsers(): void
    {
        $department = Department::firstOrCreate(['name' => 'القسم العام']);
        $specialization = Specialization::firstOrCreate(
            ['code' => 'GEN'],
            ['department_id' => $department->id, 'name' => 'التخصص العام']
        );

        $studentUser = User::updateOrCreate(
            ['email' => '20240001@student.hist.edu.ly'],
            [
                'name' => 'طالب تجريبي',
                'password' => Hash::make('password123'),
                'email_verified_at' => now(),
            ]
        );
        $studentUser->assignRole('student');

        $student = Student::updateOrCreate(
            ['registration_number' => '20240001'],
            [
                'user_id' => $studentUser->id,
                'full_name' => 'طالب تجريبي',
                'national_id' => '120240000001',
                'gender' => 'Male',
                'nationality' => 'ليبي',
                'birth_date' => '2005-01-01',
                'admission_date' => now()->toDateString(),
                'current_specialization_id' => $specialization->id,
                'current_semester_level' => 1,
                'status' => 'Active',
            ]
        );

        $teacherUser = User::updateOrCreate(
            ['email' => 'teacher@hist.edu.ly'],
            [
                'name' => 'محاضر تجريبي',
                'password' => Hash::make('password123'),
                'email_verified_at' => now(),
            ]
        );
        $teacherUser->assignRole('teacher');

        $instructorOne = Instructor::updateOrCreate(
            ['employee_id' => 'TCH001'],
            [
                'user_id' => $teacherUser->id,
                'department_id' => $department->id,
                'name' => 'محاضر تجريبي',
                'email' => 'teacher@hist.edu.ly',
                'status' => 'Active',
            ]
        );

        $teacherUserTwo = User::updateOrCreate(
            ['email' => 'teacher2@hist.edu.ly'],
            [
                'name' => 'محاضر كيمياء تطبيقية',
                'password' => Hash::make('password123'),
                'email_verified_at' => now(),
            ]
        );
        $teacherUserTwo->assignRole('teacher');

        $instructorTwo = Instructor::updateOrCreate(
            ['employee_id' => 'TCH002'],
            [
                'user_id' => $teacherUserTwo->id,
                'department_id' => $department->id,
                'name' => 'محاضر كيمياء تطبيقية',
                'email' => 'teacher2@hist.edu.ly',
                'status' => 'Active',
            ]
        );

        $semester = AcademicSemester::firstOrCreate(
            ['code' => 'SPRING-2026'],
            [
                'season' => 'Spring',
                'year' => 2026,
                'start_date' => '2026-02-08',
                'end_date' => '2026-07-07',
                'registration_start' => '2026-02-08',
                'registration_end' => '2026-02-22',
                'final_exams_start' => '2026-06-23',
            ]
        );

        $chemistry = Course::firstOrCreate(
            ['code' => 'CHEM101'],
            [
                'name' => 'الكيمياء العامة',
                'units' => 3,
                'has_practical' => true,
            ]
        );

        $chemistry->specializations()->syncWithoutDetaching([
            $specialization->id => ['semester_level' => 1],
        ]);

        $groupA = StudyGroup::firstOrCreate(
            [
                'specialization_id' => $specialization->id,
                'academic_semester_id' => $semester->id,
                'semester_level' => 1,
                'group_name' => 'المجموعة أ',
            ],
            ['capacity' => 45]
        );

        $groupB = StudyGroup::firstOrCreate(
            [
                'specialization_id' => $specialization->id,
                'academic_semester_id' => $semester->id,
                'semester_level' => 1,
                'group_name' => 'المجموعة ب',
            ],
            ['capacity' => 45]
        );

        $classA = CourseClass::updateOrCreate(
            [
                'course_id' => $chemistry->id,
                'semester_id' => $semester->id,
                'study_group_id' => $groupA->id,
            ],
            [
                'instructor_id' => $instructorOne->id,
                'group_name' => $groupA->group_name,
            ]
        );

        CourseClass::updateOrCreate(
            [
                'course_id' => $chemistry->id,
                'semester_id' => $semester->id,
                'study_group_id' => $groupB->id,
            ],
            [
                'instructor_id' => $instructorTwo->id,
                'group_name' => $groupB->group_name,
            ]
        );

        CourseEnrollment::updateOrCreate(
            [
                'student_id' => $student->id,
                'study_group_id' => $groupA->id,
                'course_id' => $chemistry->id,
            ],
            [
                'class_id' => $classA->id,
                'is_carried' => false,
                'status' => 'Pending',
            ]
        );

        $employeeUser = User::updateOrCreate(
            ['email' => 'employee@hist.edu.ly'],
            [
                'name' => 'موظف شؤون الطلبة',
                'password' => Hash::make('password123'),
                'email_verified_at' => now(),
            ]
        );
        $employeeUser->assignRole('employee');
    }
}

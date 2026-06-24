<?php

use Modules\Academic\Models\AcademicSemester;
use Modules\Academic\Models\Department;
use Modules\Academic\Models\Specialization;
use Modules\Academic\Models\StudyGroup;
use Modules\User\Models\User;
use Spatie\Permission\Models\Role;

function studyGroupAdmin(): User
{
    $user = User::factory()->create();
    $user->assignRole(Role::findOrCreate('super_admin', 'web'));

    return $user;
}

function studyGroupSpecialization(): Specialization
{
    $department = Department::create([
        'name' => 'قسم الصيدلة '.uniqid(),
        'code' => 'PH'.substr(uniqid(), -4),
    ]);

    return Specialization::create([
        'department_id' => $department->id,
        'name' => 'الصيدلة العامة '.uniqid(),
        'code' => 'SP'.substr(uniqid(), -4),
        'semesters_count' => 6,
    ]);
}

it('paginates study groups and exposes filters on the index page', function () {
    $user = studyGroupAdmin();
    $specialization = studyGroupSpecialization();
    $semester = AcademicSemester::create([
        'code' => 'SPRING-2026',
        'season' => 'Spring',
        'year' => 2026,
        'start_date' => '2026-06-01',
        'end_date' => '2026-07-10',
        'registration_start' => '2026-06-20',
        'registration_end' => '2026-06-28',
        'final_exams_start' => '2026-06-29',
    ]);

    foreach (range(1, 12) as $index) {
        StudyGroup::create([
            'specialization_id' => $specialization->id,
            'academic_semester_id' => $semester->id,
            'semester_level' => 1,
            'group_name' => 'مجموعة '.$index,
            'capacity' => 30,
        ]);
    }

    $this->actingAs($user)
        ->get(route('academic.study-groups.index'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('Academic/StudyGroups/Index')
            ->has('studyGroups.data', 10)
            ->where('studyGroups.per_page', 10)
            ->where('studyGroups.total', 12)
            ->where('creationAvailability.is_open', true)
        );
});

it('blocks opening study group creation when there is no active semester', function () {
    $user = studyGroupAdmin();

    AcademicSemester::create([
        'code' => 'FALL-2025',
        'season' => 'Fall',
        'year' => 2025,
        'start_date' => '2025-09-01',
        'end_date' => '2026-01-15',
        'registration_start' => '2025-09-01',
        'registration_end' => '2025-09-14',
        'final_exams_start' => '2026-01-01',
    ]);

    $this->actingAs($user)
        ->get(route('academic.study-groups.create'))
        ->assertRedirect(route('academic.study-groups.index'))
        ->assertSessionHasErrors('study_group_creation');
});

it('blocks study group creation when fewer than three days remain before registration closes', function () {
    $user = studyGroupAdmin();
    $specialization = studyGroupSpecialization();
    $semester = AcademicSemester::create([
        'code' => 'SPRING-2026',
        'season' => 'Spring',
        'year' => 2026,
        'start_date' => '2026-06-01',
        'end_date' => '2026-07-10',
        'registration_start' => '2026-06-20',
        'registration_end' => '2026-06-26',
        'final_exams_start' => '2026-06-29',
    ]);

    $this->actingAs($user)
        ->post(route('academic.study-groups.store'), [
            'specialization_id' => $specialization->id,
            'academic_semester_id' => $semester->id,
            'semester_level' => 1,
            'group_name' => 'أ',
            'capacity' => 40,
        ])
        ->assertRedirect(route('academic.study-groups.index'))
        ->assertSessionHasErrors('study_group_creation');
});

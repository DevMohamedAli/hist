<?php

use Modules\Academic\Models\AcademicSemester;
use Modules\User\Models\User;
use Spatie\Permission\Models\Role;

it('does not offer already-created official semester combinations on the create page', function () {
    $user = User::factory()->create();
    $user->assignRole(Role::findOrCreate('super_admin', 'web'));

    AcademicSemester::create([
        'code' => 'SPRING-2026',
        'season' => 'Spring',
        'year' => 2026,
        'start_date' => '2026-02-08',
        'end_date' => '2026-07-07',
        'registration_start' => '2026-02-08',
        'registration_end' => '2026-02-22',
        'final_exams_start' => '2026-06-23',
    ]);

    $this->actingAs($user)
        ->get(route('academic.semesters.create'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('Academic/Semesters/Create')
            ->where('creationOptions.availableCombinations.0.code', 'Fall-2026')
        );
});

it('blocks creating a duplicate official semester for the same season and year', function () {
    $user = User::factory()->create();
    $user->assignRole(Role::findOrCreate('super_admin', 'web'));

    AcademicSemester::create([
        'code' => 'SPRING-2026',
        'season' => 'Spring',
        'year' => 2026,
        'start_date' => '2026-02-08',
        'end_date' => '2026-07-07',
        'registration_start' => '2026-02-08',
        'registration_end' => '2026-02-22',
        'final_exams_start' => '2026-06-23',
    ]);

    $this->actingAs($user)
        ->post(route('academic.semesters.store'), [
            'code' => 'Spring-2026',
            'season' => 'Spring',
            'year' => 2026,
            'start_date' => '2026-02-08',
            'end_date' => '2026-07-07',
            'registration_start' => '2026-02-08',
            'registration_end' => '2026-02-22',
            'final_exams_start' => '2026-06-23',
        ])
        ->assertSessionHasErrors('season');
});

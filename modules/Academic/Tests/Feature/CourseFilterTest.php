<?php

use Inertia\Testing\AssertableInertia as Assert;
use Modules\Academic\Models\Course;
use Modules\Academic\Models\Department;
use Modules\Academic\Models\Specialization;
use Modules\User\Models\User;
use Spatie\Permission\Models\Role;

function courseFilterUser(): User
{
    $role = Role::findOrCreate('employee', 'web');
    $user = User::factory()->create();
    $user->assignRole($role);

    return $user;
}

it('filters courses by department specialization level prerequisites and curriculum status', function () {
    $suffix = substr(str_replace('.', '', uniqid('', true)), 0, 8);
    $medical = Department::create(['name' => 'Medical '.$suffix, 'code' => 'MED'.$suffix]);
    $it = Department::create(['name' => 'IT '.$suffix, 'code' => 'IT'.$suffix]);
    $pharmacy = Specialization::create([
        'department_id' => $medical->id,
        'name' => 'Pharmacy '.$suffix,
        'code' => 'PH'.$suffix,
        'semesters_count' => 6,
    ]);
    $software = Specialization::create([
        'department_id' => $it->id,
        'name' => 'Software '.$suffix,
        'code' => 'SW'.$suffix,
        'semesters_count' => 6,
    ]);
    $base = Course::create(['code' => 'BASE'.$suffix, 'name' => 'Base '.$suffix, 'units' => 2, 'has_practical' => false]);
    $pharm = Course::create(['code' => 'PHC'.$suffix, 'name' => 'Pharmacy Course '.$suffix, 'units' => 3, 'has_practical' => true]);
    $soft = Course::create(['code' => 'SWC'.$suffix, 'name' => 'Software Course '.$suffix, 'units' => 4, 'has_practical' => false]);
    $unassigned = Course::create(['code' => 'FREE'.$suffix, 'name' => 'Free Course '.$suffix, 'units' => 1, 'has_practical' => false]);

    $base->specializations()->attach($pharmacy->id, ['semester_level' => 1]);
    $pharm->specializations()->attach($pharmacy->id, ['semester_level' => 2]);
    $soft->specializations()->attach($software->id, ['semester_level' => 4]);
    $pharm->prerequisites()->attach($base->id);

    $this->actingAs(courseFilterUser())
        ->get('/academic/courses?department='.$medical->id.'&semester_level=2&prerequisite_status=with')
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Academic/Courses/Index')
            ->where('courses.data.0.id', $pharm->id)
            ->has('courses.data', 1)
            ->has('departments')
            ->has('unitOptions')
            ->has('semesterLevels'));

    $this->actingAs(courseFilterUser())
        ->get('/academic/courses?curriculum_status=unassigned')
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('courses.data.0.id', $unassigned->id)
            ->has('courses.data', 1));
});

it('keeps department and semester level filters on the same specialization row', function () {
    $suffix = substr(str_replace('.', '', uniqid('', true)), 0, 8);
    $medical = Department::create(['name' => 'Medical '.$suffix, 'code' => 'MED'.$suffix]);
    $it = Department::create(['name' => 'IT '.$suffix, 'code' => 'IT'.$suffix]);

    $medicalLevelTwo = Specialization::create([
        'department_id' => $medical->id,
        'name' => 'Medical Track '.$suffix,
        'code' => 'MD'.$suffix,
        'semesters_count' => 6,
    ]);

    $itLevelTwo = Specialization::create([
        'department_id' => $it->id,
        'name' => 'IT Track '.$suffix,
        'code' => 'IT'.$suffix,
        'semesters_count' => 6,
    ]);

    $matching = Course::create(['code' => 'MATCH'.$suffix, 'name' => 'Matching '.$suffix, 'units' => 2, 'has_practical' => false]);
    $misleading = Course::create(['code' => 'MIS'.$suffix, 'name' => 'Misleading '.$suffix, 'units' => 2, 'has_practical' => false]);

    $matching->specializations()->attach($medicalLevelTwo->id, ['semester_level' => 2]);
    $misleading->specializations()->attach($medical->id, ['semester_level' => 1]);
    $misleading->specializations()->attach($itLevelTwo->id, ['semester_level' => 2]);

    $this->actingAs(courseFilterUser())
        ->get('/academic/courses?department='.$medical->id.'&semester_level=2')
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Academic/Courses/Index')
            ->where('courses.data.0.id', $matching->id)
            ->has('courses.data', 1));
});

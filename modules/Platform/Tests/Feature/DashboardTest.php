<?php

use Modules\User\Models\User;
use Spatie\Permission\Models\Role;

test('guests are redirected to the login page', function () {
    $response = $this->get(route('dashboard'));

    $response->assertRedirect(route('login'));
});

test('dashboard redirects employees to the employee dashboard', function () {
    Role::findOrCreate('employee', 'web');
    $user = User::factory()->create();
    $user->assignRole('employee');

    $response = $this->actingAs($user)->get(route('dashboard'));

    $response->assertRedirect('/employee/dashboard');
});

test('dashboard redirects teachers to the teacher dashboard', function () {
    Role::findOrCreate('teacher', 'web');
    $user = User::factory()->create();
    $user->assignRole('teacher');

    $response = $this->actingAs($user)->get(route('dashboard'));

    $response->assertRedirect('/teacher/dashboard');
});

test('dashboard redirects students to the student dashboard', function () {
    Role::findOrCreate('student', 'web');
    $user = User::factory()->create();
    $user->assignRole('student');

    $response = $this->actingAs($user)->get(route('dashboard'));

    $response->assertRedirect('/student/dashboard');
});

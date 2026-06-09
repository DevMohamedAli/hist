<?php

use Illuminate\Support\Facades\Hash;
use Modules\Staff\Models\Instructor;
use Modules\Student\Models\Student;
use Modules\User\Models\User;
use Spatie\Permission\Models\Role;

test('student portal login redirects to student dashboard', function () {
    Role::findOrCreate('student', 'web');
    $user = User::factory()->create(['password' => Hash::make('password')]);
    $user->assignRole('student');

    Student::create([
        'user_id' => $user->id,
        'registration_number' => '123456789',
        'full_name' => 'Student User',
        'national_id' => '123456789012',
        'gender' => 'Male',
        'nationality' => 'Libyan',
        'birth_date' => '2005-01-01',
        'admission_date' => '2026-09-01',
        'status' => 'Active',
    ]);

    $response = $this->post(route('student.login.submit'), [
        'registration_number' => '123456789',
        'password' => 'password',
    ]);

    $this->assertAuthenticatedAs($user);
    $response->assertRedirect('/student/dashboard');
});

test('teacher portal login redirects to teacher dashboard', function () {
    Role::findOrCreate('teacher', 'web');
    $user = User::factory()->create(['password' => Hash::make('password')]);
    $user->assignRole('teacher');

    Instructor::create([
        'user_id' => $user->id,
        'employee_id' => 'T-100',
        'name' => 'Teacher User',
        'status' => 'Active',
    ]);

    $response = $this->post(route('teacher.login.submit'), [
        'employee_id' => 'T-100',
        'password' => 'password',
    ]);

    $this->assertAuthenticatedAs($user);
    $response->assertRedirect('/teacher/dashboard');
});

test('employee portal login redirects to employee dashboard', function () {
    Role::findOrCreate('employee', 'web');
    $user = User::factory()->create(['password' => Hash::make('password')]);
    $user->assignRole('employee');

    $response = $this->post(route('employee.login.submit'), [
        'email' => $user->email,
        'password' => 'password',
    ]);

    $this->assertAuthenticatedAs($user);
    $response->assertRedirect('/employee/dashboard');
});

test('dismissed students cannot login to the student portal', function () {
    Role::findOrCreate('student', 'web');
    $user = User::factory()->create(['password' => Hash::make('password')]);
    $user->assignRole('student');

    Student::create([
        'user_id' => $user->id,
        'registration_number' => '987654321',
        'full_name' => 'Dismissed Student',
        'national_id' => '210987654321',
        'gender' => 'Female',
        'nationality' => 'Libyan',
        'birth_date' => '2005-01-01',
        'admission_date' => '2026-09-01',
        'status' => 'Dismissed',
    ]);

    $response = $this->post(route('student.login.submit'), [
        'registration_number' => '987654321',
        'password' => 'password',
    ]);

    $this->assertGuest();
    $response->assertSessionHasErrors('registration_number');
});

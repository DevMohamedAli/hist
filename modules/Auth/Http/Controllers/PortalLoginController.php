<?php

namespace Modules\Auth\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Modules\Shared\Http\Controllers\Controller;
use Modules\Staff\Models\Instructor;
use Modules\Student\Models\Student;
use Modules\Auth\Support\RoleDashboard;
use Modules\User\Models\User;

class PortalLoginController extends Controller
{
    public function studentLogin(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'registration_number' => 'required|string',
            'password' => 'required|string',
        ]);

        $student = Student::with('user')->where('registration_number', $validated['registration_number'])->first();

        if ($student && in_array($student->status, ['Dismissed', 'Withdrawn'], true)) {
            return back()->withErrors([
                'registration_number' => 'لا يمكن الدخول: حالة الطالب لا تسمح باستخدام البوابة.',
            ])->onlyInput('registration_number');
        }

        return $this->attemptPortalLogin(
            user: $student?->user,
            password: $validated['password'],
            role: 'student',
            errorKey: 'registration_number',
            redirectTo: RoleDashboard::pathFor($student?->user)
        );
    }

    public function teacherLogin(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'employee_id' => 'required|string',
            'password' => 'required|string',
        ]);

        $instructor = Instructor::with('user')->where('employee_id', $validated['employee_id'])->first();

        if ($instructor && $instructor->status === 'Suspended') {
            return back()->withErrors([
                'employee_id' => 'لا يمكن الدخول: حساب المحاضر موقوف.',
            ])->onlyInput('employee_id');
        }

        return $this->attemptPortalLogin(
            user: $instructor?->user,
            password: $validated['password'],
            role: 'teacher',
            errorKey: 'employee_id',
            redirectTo: RoleDashboard::pathFor($instructor?->user)
        );
    }

    public function employeeLogin(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        $user = User::where('email', $validated['email'])->first();

        if ($user && ! $user->hasAnyRole(['employee', 'super_admin'])) {
            return back()->withErrors([
                'email' => 'غير مصرح لك بالدخول من هذه البوابة.',
            ])->onlyInput('email');
        }

        return $this->attemptPortalLogin(
            user: $user,
            password: $validated['password'],
            role: null,
            errorKey: 'email',
            redirectTo: RoleDashboard::pathFor($user)
        );
    }

    private function attemptPortalLogin(?User $user, string $password, ?string $role, string $errorKey, string $redirectTo): RedirectResponse
    {
        if (! $user || ! Hash::check($password, $user->password)) {
            return back()->withErrors([
                $errorKey => 'بيانات الدخول غير صحيحة.',
            ])->onlyInput($errorKey);
        }

        if ($role && ! $user->hasRole($role)) {
            return back()->withErrors([
                $errorKey => 'غير مصرح لك بالدخول من هذه البوابة.',
            ])->onlyInput($errorKey);
        }

        Auth::login($user);
        request()->session()->regenerate();

        return redirect()->to($redirectTo);
    }
}

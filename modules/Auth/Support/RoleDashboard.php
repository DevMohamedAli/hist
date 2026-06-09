<?php

namespace Modules\Auth\Support;

use Modules\User\Models\User;

class RoleDashboard
{
    public static function pathFor(?User $user): string
    {
        if (! $user) {
            return '/';
        }

        if ($user->hasAnyRole(['super_admin', 'employee'])) {
            return '/employee/dashboard';
        }

        if ($user->hasRole('teacher')) {
            return '/teacher/dashboard';
        }

        if ($user->hasRole('student')) {
            return '/student/dashboard';
        }

        return '/';
    }
}

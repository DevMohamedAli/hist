<?php

namespace Modules\Platform\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();

        if (! $user) {
            abort(403, 'يجب تسجيل الدخول أولا.');
        }

        if ($user->hasRole('super_admin')) {
            return $next($request);
        }

        if (! $user->hasAnyRole($roles)) {
            abort(403, 'غير مصرح لك بالدخول إلى هذه الصفحة.');
        }

        return $next($request);
    }
}

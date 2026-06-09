<?php

namespace Modules\Auth\Http\Responses;

use Laravel\Fortify\Contracts\LoginResponse as LoginResponseContract;
use Modules\Auth\Support\RoleDashboard;

class LoginResponse implements LoginResponseContract
{
    public function toResponse($request)
    {
        return redirect()->to(RoleDashboard::pathFor($request->user()));
    }
}

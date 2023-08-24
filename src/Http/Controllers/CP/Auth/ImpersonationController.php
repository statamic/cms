<?php

namespace Statamic\Http\Controllers\CP\Auth;

use Illuminate\Support\Facades\Auth;
use Statamic\Facades\User;

class ImpersonationController
{
    public function stop()
    {
        if ($originalUserId = session()->pull('statamic_impersonated_by')) {
            $guard = Auth::guard();

            $originalUser = User::find($originalUserId);

            $guard->login($originalUser);
        }

        return redirect()->route('statamic.cp.users.index');
    }
}

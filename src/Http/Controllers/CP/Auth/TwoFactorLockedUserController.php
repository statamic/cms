<?php

namespace Statamic\Http\Controllers\CP\Auth;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Statamic\Facades\User;

class TwoFactorLockedUserController
{
    public function index(Request $request)
    {
        // This is in a separate controller because the route SHOULD be visible while you are logged in,
        // but exceeding your limit, as this controller will log you out - so if you refresh, you're back
        // at the login view.
        //
        // This means it works from the "challenge" view within the CP, as well as the initial setup stage

        // if the user is NOT locked, go back to the default CP route
        if (! User::current()->two_factor_locked) {
            return redirect(cp_route('index'));
        }

        // log the user out of the right guard
        Auth::guard(config('statamic.users.guards.cp', null))->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        // show the lock view
        return view('statamic::auth.two-factor.locked');
    }
}

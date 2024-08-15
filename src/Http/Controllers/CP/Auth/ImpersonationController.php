<?php

namespace Statamic\Http\Controllers\CP\Auth;

use Illuminate\Events\NullDispatcher;
use Illuminate\Support\Facades\Auth;
use Statamic\Events\ImpersonationEnded;
use Statamic\Facades\User;

class ImpersonationController
{
    public function stop()
    {
        if ($originalUserId = session()->pull('statamic_impersonated_by')) {
            $guard = Auth::guard();

            $dispatcher = $guard->getDispatcher();

            if ($dispatcher) {
                $guard->setDispatcher(new NullDispatcher($dispatcher));
            }

            $impersonatedUser = User::current();
            $originalUser = User::find($originalUserId);

            if ($originalUser) {
                $guard->login($originalUser);
            }

            if ($dispatcher) {
                $guard->setDispatcher($dispatcher);
            }

            ImpersonationEnded::dispatch($originalUser, $impersonatedUser);
        }

        return redirect()->route('statamic.cp.users.index');
    }
}

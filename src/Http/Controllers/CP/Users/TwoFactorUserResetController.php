<?php

namespace Statamic\Http\Controllers\CP\Users;

use Illuminate\Http\Request;
use Statamic\Auth\TwoFactor\DisableTwoFactorAuthentication;
use Statamic\Facades\TwoFactorUser;
use Statamic\Facades\User;

class TwoFactorUserResetController
{
    public function destroy(Request $request, DisableTwoFactorAuthentication $disable)
    {
        $requestingUser = User::current();
        $user = User::find($request->user);

        // can they edit the user (or themselves)?
        if (! $requestingUser->can('edit', $user)) {
            abort(403);
        }

        // disable two factor
        $disable($user);

        // redirect
        // if two factor is enforcable, and the same user, log them out
        $redirect = null;
        if ($user->id === $requestingUser->id && TwoFactorUser::isTwoFactorEnforceable()) {
            $redirect = cp_route('logout');
        }

        // success
        return [
            'redirect' => $redirect,
        ];
    }
}

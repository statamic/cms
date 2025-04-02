<?php

namespace Statamic\Http\Controllers\CP\Users;

use Illuminate\Http\Request;
use Statamic\Auth\TwoFactor\CreateRecoveryCodes;
use Statamic\Facades\User;

class TwoFactorRecoveryCodesController
{
    public function show(Request $request)
    {
        $requestingUser = User::current();
        $user = User::find($request->user);

        // can only see recovery codes for themselves
        if ($requestingUser->id !== $user->id) {
            abort(403);
        }

        // success
        return [
            'recovery_codes' => json_decode(decrypt(User::current()->two_factor_recovery_codes), true),
        ];
    }

    public function store(Request $request, CreateRecoveryCodes $createRecoveryCodes)
    {
        $requestingUser = User::current();
        $user = User::find($request->user);

        // can only generate recovery codes for themselves
        if ($requestingUser->id !== $user->id) {
            abort(403);
        }

        // create new recovery codes
        $createRecoveryCodes($user);

        // success
        return [
            'recovery_codes' => json_decode(decrypt($user->two_factor_recovery_codes), true),
        ];
    }
}

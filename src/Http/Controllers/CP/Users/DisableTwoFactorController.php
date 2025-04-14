<?php

namespace Statamic\Http\Controllers\CP\Users;

use Illuminate\Http\Request;
use Statamic\Auth\TwoFactor\DisableTwoFactorAuthentication;
use Statamic\Exceptions\NotFoundHttpException;
use Statamic\Facades\User;

class DisableTwoFactorController
{
    public function __invoke(Request $request, $user, DisableTwoFactorAuthentication $disable)
    {
        throw_unless($user = User::find($user), new NotFoundHttpException);

        if (! $request->user()->can('edit', $user)) {
            abort(403);
        }

        $disable($user);

        if ($request->user()->id === $user->id && $user->isTwoFactorAuthRequired()) {
            return ['redirect' => cp_route('logout')];
        }

        return ['redirect' => null];
    }
}

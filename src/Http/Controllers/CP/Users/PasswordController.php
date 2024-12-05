<?php

namespace Statamic\Http\Controllers\CP\Users;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Password as PasswordFacade;
use Illuminate\Validation\Rules\Password;
use Statamic\Events\UserPasswordChanged;
use Statamic\Exceptions\NotFoundHttpException;
use Statamic\Facades\User;
use Statamic\Http\Controllers\CP\CpController;

class PasswordController extends CpController
{
    public function update(Request $request, $user)
    {
        throw_unless($user = User::find($user), new NotFoundHttpException);

        $updatingOwnPassword = $user->id() == User::fromUser($request->user())->id();

        $this->authorize('editPassword', $user);

        $rules = [
            'password' => ['required', 'confirmed', Password::default()],
        ];

        if ($updatingOwnPassword) {
            $rules['current_password'] = ['required', 'current_password'];
        }

        $request->validate($rules);

        $user->password($request->password)->save();

        if ($updatingOwnPassword) {
            Auth::login($user);
        }

        PasswordFacade::deleteToken($user);

        UserPasswordChanged::dispatch($user);

        return response('', 204);
    }
}

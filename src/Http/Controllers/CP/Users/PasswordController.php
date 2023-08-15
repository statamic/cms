<?php

namespace Statamic\Http\Controllers\CP\Users;

use Illuminate\Http\Request;
use Illuminate\Validation\Rules\Password;
use Statamic\Exceptions\NotFoundHttpException;
use Statamic\Facades\User;
use Statamic\Http\Controllers\CP\CpController;

class PasswordController extends CpController
{
    public function update(Request $request, $user)
    {
        throw_unless($user = User::find($user), new NotFoundHttpException);

        $this->authorize('editPassword', $user);

        $request->validate([
            'current_password' => ['required', 'current_password'],
            'password' => ['required', 'confirmed', Password::default()],
        ]);

        $user->password($request->password)->save();

        return response('', 204);
    }
}

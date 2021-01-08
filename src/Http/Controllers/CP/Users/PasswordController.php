<?php

namespace Statamic\Http\Controllers\CP\Users;

use Illuminate\Http\Request;
use Statamic\Facades\User;
use Statamic\Http\Controllers\CP\CpController;

class PasswordController extends CpController
{
    public function update(Request $request, $user)
    {
        $user = User::find($user);

        $this->authorize('editPassword', $user);

        $request->validate([
            'password' => 'required|confirmed',
        ]);

        $user->password($request->password)->save();

        return response('', 204);
    }
}

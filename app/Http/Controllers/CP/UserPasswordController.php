<?php

namespace Statamic\Http\Controllers\CP;

use Statamic\API\User;
use Illuminate\Http\Request;

class UserPasswordController extends CpController
{
    public function update(Request $request, $user)
    {
        $user = User::find($user);

        // TODO: Authorization

        $request->validate([
            'password' => 'required|confirmed'
        ]);

        $user->password($request->password)->save();

        return response('', 204);
    }
}

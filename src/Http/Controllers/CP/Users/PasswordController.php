<?php

namespace Statamic\Http\Controllers\CP\Users;

use Illuminate\Http\Request;
use Statamic\Http\Controllers\CP\CpController;

class PasswordController extends CpController
{
    public function update(Request $request, $user)
    {
        $this->authorize('editPassword', $user);

        $request->validate([
            'password' => 'required|confirmed',
        ]);

        $user->password($request->password)->save();

        return response('', 204);
    }
}

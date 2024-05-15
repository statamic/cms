<?php

namespace Statamic\Http\Controllers\User;

use Statamic\Facades\User;
use Statamic\Http\Requests\UserPasswordRequest;

class PasswordController
{
    public function password(UserPasswordRequest $request)
    {
        $user = User::current();

        $user->password($request->password);

        $user->save();

        return $this->userPasswordSuccess();
    }

    public function userPasswordSuccess()
    {
        $response = request()->has('_redirect') ? redirect(request()->get('_redirect')) : back();

        if (request()->ajax() || request()->wantsJson()) {
            return response([
                'success' => true,
                'redirect' => $response->getTargetUrl(),
            ]);
        }

        session()->flash('user.password.success', __('Change successful.'));

        return $response;
    }
}

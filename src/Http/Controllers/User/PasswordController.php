<?php

namespace Statamic\Http\Controllers\User;

use Illuminate\Support\Facades\Password;
use Statamic\Facades\User;
use Statamic\Http\Requests\UserPasswordRequest;

class PasswordController
{
    public function __invoke(UserPasswordRequest $request)
    {
        $user = User::current();

        $user->password($request->password)->save();

        Password::deleteToken($user);

        return $this->successfulResponse();
    }

    private function successfulResponse()
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

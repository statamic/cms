<?php

namespace Statamic\Http\Controllers\User;

use Illuminate\Support\Facades\Auth;
use Statamic\Auth\ThrottlesLogins;
use Statamic\Http\Controllers\Controller;
use Statamic\Http\Requests\UserLoginRequest;

class LoginController extends Controller
{
    use ThrottlesLogins;

    public function login(UserLoginRequest $request)
    {
        if ($this->hasTooManyLoginAttempts($request)) {
            $this->fireLockoutEvent($request);

            return $this->sendLockoutResponse($request);
        }

        if (Auth::attempt($request->only('email', 'password'), $request->has('remember'))) {
            return redirect($request->input('_redirect', '/'))->withSuccess(__('Login successful.'));
        }

        $this->incrementLoginAttempts($request);

        $errorResponse = $request->has('_error_redirect') ? redirect($request->input('_error_redirect')) : back();

        return $errorResponse->withInput()->withErrors(__('Invalid credentials.'));
    }

    public function logout()
    {
        Auth::logout();

        return redirect(request()->get('redirect', '/'));
    }

    protected function username()
    {
        return 'email';
    }
}

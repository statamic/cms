<?php

namespace Statamic\Http\Controllers\User;

use Illuminate\Support\Facades\Auth;
use Statamic\Auth\ThrottlesLogins;
use Statamic\Facades\URL;
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
            $redirect = $request->input('_redirect', '/');

            return redirect(URL::isExternalToApplication($redirect) ? '/' : $redirect)->withSuccess(__('Login successful.'));
        }

        $this->incrementLoginAttempts($request);

        $errorRedirect = $request->input('_error_redirect');

        $errorResponse = $errorRedirect && ! URL::isExternalToApplication($errorRedirect)
            ? redirect($errorRedirect)
            : back();

        return $errorResponse->withInput()->withErrors(__('Invalid credentials.'));
    }

    public function logout()
    {
        Auth::logout();

        $redirect = request()->get('redirect', '/');

        return redirect(URL::isExternalToApplication($redirect) ? '/' : $redirect);
    }

    protected function username()
    {
        return 'email';
    }
}

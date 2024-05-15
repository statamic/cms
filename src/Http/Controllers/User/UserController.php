<?php

namespace Statamic\Http\Controllers\User;

use Illuminate\Support\Facades\Auth;
use Statamic\Auth\ThrottlesLogins;
use Statamic\Facades\User;
use Statamic\Http\Controllers\Controller;
use Statamic\Http\Requests\UserLoginRequest;
use Statamic\Http\Requests\UserPasswordRequest;
use Statamic\Http\Requests\UserProfileRequest;

class UserController extends Controller
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

    public function profile(UserProfileRequest $request)
    {
        $user = User::current();

        if ($request->email) {
            $user->email($request->email);
        }

        foreach ($request->processedValues() as $key => $value) {
            $user->set($key, $value);
        }

        $user->save();

        return $this->userProfileSuccess();
    }

    public function password(UserPasswordRequest $request)
    {
        $user = User::current();

        $user->password($request->password);

        $user->save();

        return $this->userPasswordSuccess();
    }

    public function username()
    {
        return 'email';
    }

    private function userProfileSuccess(bool $silentFailure = false)
    {
        $response = request()->has('_redirect') ? redirect(request()->get('_redirect')) : back();

        if (request()->ajax() || request()->wantsJson()) {
            return response([
                'success' => true,
                'user_updated' => ! $silentFailure,
                'redirect' => $response->getTargetUrl(),
            ]);
        }

        session()->flash('user.profile.success', __('Update successful.'));

        return $response;
    }

    private function userPasswordSuccess(bool $silentFailure = false)
    {
        $response = request()->has('_redirect') ? redirect(request()->get('_redirect')) : back();

        if (request()->ajax() || request()->wantsJson()) {
            return response([
                'success' => true,
                'password_updated' => ! $silentFailure,
                'redirect' => $response->getTargetUrl(),
            ]);
        }

        session()->flash('user.password.success', __('Change successful.'));

        return $response;
    }
}

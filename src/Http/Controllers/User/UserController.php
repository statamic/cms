<?php

namespace Statamic\Http\Controllers\User;

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\MessageBag;
use Illuminate\Validation\ValidationException;
use Statamic\Auth\ThrottlesLogins;
use Statamic\Events\UserRegistered;
use Statamic\Events\UserRegistering;
use Statamic\Exceptions\SilentFormFailureException;
use Statamic\Facades\User;
use Statamic\Http\Controllers\Controller;
use Statamic\Http\Requests\UserLoginRequest;
use Statamic\Http\Requests\UserPasswordRequest;
use Statamic\Http\Requests\UserProfileRequest;
use Statamic\Http\Requests\UserRegisterRequest;

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

    public function register(UserRegisterRequest $request)
    {
        $user = User::make()
            ->email($request->email)
            ->password($request->password)
            ->data($request->processedValues());

        if ($roles = config('statamic.users.new_user_roles')) {
            $user->explicitRoles($roles);
        }

        if ($groups = config('statamic.users.new_user_groups')) {
            $user->groups($groups);
        }

        try {
            if ($honeypot = config('statamic.users.registration_form_honeypot_field')) {
                throw_if(Arr::get($request->input(), $honeypot), new SilentFormFailureException);
            }

            throw_if(UserRegistering::dispatch($user) === false, new SilentFormFailureException);
        } catch (ValidationException $e) {
            return $this->userRegistrationFailure($e);
        } catch (SilentFormFailureException $e) {
            return $this->userRegistrationSuccess(true);
        }

        $user->save();

        UserRegistered::dispatch($user);

        Auth::login($user);

        return $this->userRegistrationSuccess();
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

    private function userRegistrationFailure($validator)
    {
        $errors = $validator->errors();

        if (request()->ajax()) {
            return response([
                'errors' => (new MessageBag($errors))->all(),
                'error' => collect($errors)->map(function ($errors, $field) {
                    return $errors[0];
                })->all(),
            ], 400);
        }

        if (request()->wantsJson()) {
            return (new ValidationException($validator))->errorBag(new MessageBag($errors));
        }

        $errorResponse = request()->has('_error_redirect') ? redirect(request()->input('_error_redirect')) : back();

        return $errorResponse->withInput()->withErrors($errors, 'user.register');
    }

    private function userRegistrationSuccess(bool $silentFailure = false)
    {
        $response = request()->has('_redirect') ? redirect(request()->get('_redirect')) : back();

        if (request()->ajax() || request()->wantsJson()) {
            return response([
                'success' => true,
                'user_created' => ! $silentFailure,
                'redirect' => $response->getTargetUrl(),
            ]);
        }

        session()->flash('user.register.success', __('Registration successful.'));
        session()->flash('user.register.user_created', ! $silentFailure);

        return $response;
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

<?php

namespace Statamic\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Statamic\Auth\Passwords\PasswordDefaults;
use Statamic\Auth\ThrottlesLogins;
use Statamic\Events\UserRegistered;
use Statamic\Events\UserRegistering;
use Statamic\Exceptions\SilentFormFailureException;
use Statamic\Facades\User;

class UserController extends Controller
{
    use ThrottlesLogins;

    private $request;

    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required',
            'password' => 'required',
        ]);

        if ($validator->passes()) {
            if ($this->hasTooManyLoginAttempts($request)) {
                $this->fireLockoutEvent($request);

                return $this->sendLockoutResponse($request);
            }

            if (Auth::attempt($request->only('email', 'password'), $request->has('remember'))) {
                return redirect($request->input('_redirect', '/'))->withSuccess(__('Login successful.'));
            }

            $this->incrementLoginAttempts($request);
        }

        $errorResponse = $request->has('_error_redirect') ? redirect($request->input('_error_redirect')) : back();

        return $errorResponse->withInput()->withErrors(__('Invalid credentials.'));
    }

    public function logout()
    {
        Auth::logout();

        return redirect(request()->get('redirect', '/'));
    }

    public function register(Request $request)
    {
        $blueprint = User::blueprint();

        $fields = $blueprint->fields()->addValues($request->all());

        $fieldRules = $fields->validator()->withRules([
            'email' => ['required', 'email', 'unique_user_value'],
            'password' => ['required', 'confirmed', PasswordDefaults::rules()],
        ])->rules();

        $validator = Validator::make($request->all(), $fieldRules);

        if ($validator->fails()) {
            return $this->userRegistrationFailure($validator->errors());
        }

        $values = $fields->process()->values()->except(['email', 'groups', 'roles']);

        $user = User::make()
            ->email($request->email)
            ->password($request->password)
            ->data($values);

        if ($roles = config('statamic.users.new_user_roles')) {
            $user->roles($roles);
        }

        if ($groups = config('statamic.users.new_user_groups')) {
            $user->groups($groups);
        }

        try {
            throw_if(UserRegistering::dispatch($user) === false, new SilentFormFailureException);
        } catch (ValidationException $e) {
            return $this->userRegistrationFailure($e->errors());
        } catch (SilentFormFailureException $e) {
            return $this->userRegistrationSuccess(true);
        }

        $user->save();

        UserRegistered::dispatch($user);

        Auth::login($user);

        return $this->userRegistrationSuccess();
    }

    public function username()
    {
        return 'email';
    }

    private function userRegistrationFailure($errors = null)
    {
        $errorResponse = request()->has('_error_redirect') ? redirect(request()->input('_error_redirect')) : back();

        return $errorResponse->withInput()->withErrors($errors, 'user.register');
    }

    private function userRegistrationSuccess(bool $silentFailure = false)
    {
        $response = request()->has('_redirect') ? redirect(request()->get('_redirect')) : back();

        session()->flash('user.register.success', __('Registration successful.'));
        session()->flash('user.register.user_created', ! $silentFailure);

        return $response;
    }
}

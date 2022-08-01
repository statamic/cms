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
use Statamic\Exceptions\UnauthorizedHttpException;
use Statamic\Facades\User;
use Statamic\Forms\Uploaders\AssetsUploader;
use Statamic\Support\Arr;

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

        $fields = $blueprint->fields();
        $values = array_merge($request->all(), $this->normalizeAssetValues($fields, $request));
        $fields = $fields->addValues($values);

        $fieldRules = $fields->validator()->withRules(array_merge([
            'email' => ['required', 'email', 'unique_user_value'],
            'password' => ['required', 'confirmed', PasswordDefaults::rules()],
        ], $this->assetRules($fields)))->rules();

        $validator = Validator::make($request->all(), $fieldRules);

        if ($validator->fails()) {
            return $this->userRegistrationFailure($validator->errors());
        }

        $values = array_merge($request->all(), $this->uploadAssetFiles($fields));
        $fields = $fields->addValues($values);
        $values = $fields->process()->values()->only(array_keys($values))->except(['email', 'groups', 'roles']);

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

    public function profile(Request $request)
    {
        throw_unless($user = User::current(), new UnauthorizedHttpException(403));

        $blueprint = User::blueprint();

        $fields = $blueprint->fields();
        $values = array_merge($request->all(), $this->normalizeAssetValues($fields, $request));
        $fields = $fields->addValues($values);

        $fieldRules = $fields->validator()->withRules(array_merge([
            'email' => ['required', 'email', 'unique_user_value:'.$user->id()],
        ], $this->assetRules($fields)))->rules();

        $validator = Validator::make($values, $fieldRules);

        if ($validator->fails()) {
            $errors = $validator->errors();

            return back()->withInput()->withErrors($errors, 'user.profile');
        }

        $values = array_merge($request->all(), $this->uploadAssetFiles($fields));
        $fields = $fields->addValues($values);
        $values = $fields->process()->values()->only(array_keys($values))->except(['email', 'password', 'groups', 'roles']);

        if ($request->email) {
            $user->email($request->email);
        }
        foreach ($values as $key => $value) {
            $user->set($key, $value);
        }

        $user->save();

        session()->flash('user.profile.success', __('Update successful.'));

        return request()->has('_redirect') ? redirect(request()->get('_redirect')) : back();
    }

    public function changePassword(Request $request)
    {
        throw_unless($user = User::current(), new UnauthorizedHttpException(403));

        $validator = Validator::make($request->all(), [
            'current_password' => ['required', 'current_password'],
            'password'         => ['required', 'confirmed', PasswordDefaults::rules()],
        ]);

        if ($validator->fails()) {
            $errors = $validator->errors();

            return back()->withInput()->withErrors($errors, 'user.password');
        }

        $user->password($request->password);

        $user->save();

        session()->flash('user.password.success', __('Change successful.'));

        return request()->has('_redirect') ? redirect(request()->get('_redirect')) : back();
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

    protected function assetRules($fields)
    {
        return $fields->all()
            ->filter(function ($field) {
                return $field->fieldtype()->handle() === 'assets';
            })
            ->mapWithKeys(function ($field) {
                return [$field->handle().'.*' => 'file'];
            })
            ->all();
    }

    protected function normalizeAssetValues($fields, $request)
    {
        return $fields->all()
            ->filter(function ($field) {
                return $field->fieldtype()->handle() === 'assets' && $field->get('max_files') === 1;
            })
            ->map(function ($field) use ($request) {
                return Arr::wrap($request->file($field->handle()));
            })
            ->all();
    }

    protected function uploadAssetFiles($fields)
    {
        return $fields->all()
            ->filter(function ($field) {
                return $field->fieldtype()->handle() === 'assets' && request()->hasFile($field->handle());
            })
            ->map(function ($field) {
                return AssetsUploader::field($field)->upload(request()->file($field->handle()));
            })
            ->all();
    }

    protected function filterEmptyAssetFields($fields)
    {
        return $fields->all()
            ->filter(function ($field) {
                return $field->fieldtype()->handle() !== 'assets' || request()->hasFile($field->handle());
            })
            ->all();
    }
}

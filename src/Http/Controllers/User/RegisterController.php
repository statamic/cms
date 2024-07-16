<?php

namespace Statamic\Http\Controllers\User;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\MessageBag;
use Illuminate\Validation\ValidationException;
use Statamic\Events\UserRegistered;
use Statamic\Events\UserRegistering;
use Statamic\Exceptions\SilentFormFailureException;
use Statamic\Facades\User;
use Statamic\Http\Requests\UserRegisterRequest;
use Statamic\Support\Arr;

class RegisterController
{
    public function __invoke(UserRegisterRequest $request)
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
            return $this->failureResponse($e);
        } catch (SilentFormFailureException $e) {
            return $this->successfulResponse(silentFailure: true);
        }

        $user->save();

        UserRegistered::dispatch($user);

        Auth::login($user);

        return $this->successfulResponse();
    }

    private function successfulResponse(bool $silentFailure = false)
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

    private function failureResponse($validator)
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
}

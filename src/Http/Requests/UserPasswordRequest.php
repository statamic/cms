<?php

namespace Statamic\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\ValidationException;
use Statamic\Facades\User;

class UserPasswordRequest extends FormRequest
{
    public function authorize(): bool
    {
        return User::current() ? true : false;
    }

    public function rules(): array
    {
        return [
            'current_password' => ['required', 'current_password'],
            'password' => ['required', 'confirmed', Password::default()],
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        $errorResponse = $this->has('_error_redirect') ? redirect($this->input('_error_redirect')) : back();

        throw (new ValidationException($validator, $errorResponse->withInput()->withErrors($validator->errors(), 'user.password')));
    }
}

<?php

namespace Statamic\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\ValidationException;

class UserLoginRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'email' => 'required',
            'password' => 'required',
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        $errorResponse = $this->has('_error_redirect') ? redirect($this->input('_error_redirect')) : back();

        throw (new ValidationException($validator, $errorResponse->withInput()->withErrors(__('Invalid credentials.'))))
            ->errorBag($this->errorBag);
    }
}

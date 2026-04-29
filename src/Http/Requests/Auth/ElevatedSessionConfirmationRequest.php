<?php

namespace Statamic\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;

class ElevatedSessionConfirmationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'password' => ['required_without_all:verification_code,id'],
            'verification_code' => ['required_without_all:password,id'],
            'id' => ['required_without_all:password,verification_code'],
        ];
    }

    public function messages(): array
    {
        return [
            'password.required_without_all' => __('statamic::validation.required'),
            'verification_code.required_without_all' => __('statamic::validation.required'),
            'id.required_without_all' => __('statamic::validation.required'),
        ];
    }
}

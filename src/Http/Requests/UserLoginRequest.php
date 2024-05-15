<?php

namespace Statamic\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Traits\Localizable;
use Illuminate\Validation\ValidationException;
use Statamic\Facades\Site;

class UserLoginRequest extends FormRequest
{
    use Localizable;

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
        if ($this->isPrecognitive() || $this->wantsJson()) {
            return parent::failedValidation($validator);
        }

        if ($this->ajax()) {
            $errors = $validator->errors();

            $response = response([
                'errors' => $errors->all(),
                'error' => collect($errors->messages())->map(function ($errors, $field) {
                    return $errors[0];
                })->all(),
            ], 400);

            throw (new ValidationException($validator, $response));
        }

        $errorResponse = $this->has('_error_redirect') ? redirect($this->input('_error_redirect')) : back();

        throw (new ValidationException($validator, $errorResponse->withInput()->withErrors(__('Invalid credentials.'))));
    }

    public function validateResolved()
    {
        $site = Site::findByUrl(URL::previous()) ?? Site::default();

        return $this->withLocale($site->lang(), fn () => parent::validateResolved());
    }
}

<?php

namespace Statamic\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Validator as ValidatorFacade;
use Illuminate\Support\Traits\Localizable;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\ValidationException;
use Statamic\Facades\Site;
use Statamic\Facades\User;
use Statamic\Rules\UniqueUserValue;

class UserRegisterRequest extends FormRequest
{
    use Localizable;

    public $blueprintFields;
    public $submittedValues;

    public function authorize(): bool
    {
        return true;
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

        throw (new ValidationException($validator, $errorResponse->withInput()->withErrors($validator->errors(), 'user.register')));
    }

    public function processedValues()
    {
        return $this->blueprintFields->process()->values()
            ->only(array_keys($this->submittedValues))
            ->except(['email', 'groups', 'roles', 'super']);
    }

    public function validator()
    {
        $blueprint = User::blueprint();

        $fields = $blueprint->fields();
        $this->submittedValues = $this->valuesWithoutAssetFields($fields);
        $this->blueprintFields = $fields->addValues($this->submittedValues);

        $fieldRules = $this->blueprintFields
            ->validator()
            ->withRules([
                'email' => ['required', 'email', new UniqueUserValue],
                'password' => ['required', 'confirmed', Password::default()],
            ])
            ->rules();

        // should this not return ->validator() from fieldrules?
        // tests would need updated to expect labels instead of handles
        return ValidatorFacade::make($this->submittedValues, $fieldRules);
    }

    public function validateResolved()
    {
        $site = Site::findByUrl(URL::previous()) ?? Site::default();

        return $this->withLocale($site->lang(), fn () => parent::validateResolved());
    }

    private function valuesWithoutAssetFields($fields)
    {
        $assets = $fields->all()
            ->filter(fn ($field) => $field->fieldtype()->handle() === 'assets')
            ->keys()->all();

        return $this->except($assets);
    }
}

<?php

namespace Statamic\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Traits\Localizable;
use Illuminate\Validation\ValidationException;
use Statamic\Facades\Site;
use Statamic\Facades\User;
use Statamic\Rules\UniqueUserValue;

class UserProfileRequest extends FormRequest
{
    use Localizable;

    public $blueprintFields;
    public $submittedValues;

    public function authorize(): bool
    {
        return User::current() ? true : false;
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

        throw (new ValidationException($validator, $errorResponse->withInput()->withErrors($validator->errors(), 'user.profile')));
    }

    public function processedValues()
    {
        return $this->blueprintFields->process()->values()
            ->only(array_keys($this->submittedValues))
            ->except(['email', 'password', 'groups', 'roles', 'super']);
    }

    public function validator()
    {
        $blueprint = User::blueprint();

        $fields = $blueprint->fields();
        $this->submittedValues = $this->valuesWithoutAssetFields($fields);
        $this->blueprintFields = $fields->addValues($this->submittedValues);

        $userId = User::current()->id();

        return $this->blueprintFields
            ->validator()
            ->withRules(['email' => ['required', 'email', new UniqueUserValue(except: $userId)]])
            ->withReplacements(['id' => $userId])
            ->validator();
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

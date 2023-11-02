<?php

namespace Statamic\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Traits\Localizable;
use Illuminate\Validation\ValidationException;
use Statamic\Facades\Site;
use Statamic\Facades\User;

class UserProfileRequest extends FormRequest
{
    use Localizable;

    public $blueprintFields;
    public $submittedValues;

    public function authorize(): bool
    {
        return User::current() ? true : false;
    }

    public function attributes(): array
    {
        $site = Site::findByUrl(URL::previous()) ?? Site::default();

        $attributes = [];
        $this->withLocale($site->lang(), function () use (&$attributes) {
            $attributes = $this->blueprintFields
                ->validator()
                ->attributes();
        });

        return $attributes;
    }

    public function messages(): array
    {
        $site = Site::findByUrl(URL::previous()) ?? Site::default();

        $messages = [];
        $this->withLocale($site->lang(), function () use (&$messages) {
            $messages = $this->blueprintFields
                ->validator()
                ->validator()
                ->messages()
                ->getMessages();
        });

        return collect($messages)->flatten(1)->all();
    }

    public function rules(): array
    {
        $blueprint = User::blueprint();

        $fields = $blueprint->fields();
        $this->submittedValues = $this->valuesWithoutAssetFields($fields);
        $this->blueprintFields = $fields->addValues($this->submittedValues);

        $rules = $this->blueprintFields->validator()->withRules([
            'email' => ['required', 'email', 'unique_user_value:{id}'],
        ])->withReplacements([
            'id' => User::current()->id(),
        ])->rules();

        return $rules;
    }

    protected function failedValidation(Validator $validator)
    {
        $errorResponse = $this->has('_error_redirect') ? redirect($this->input('_error_redirect')) : back();

        throw (new ValidationException($validator, $errorResponse->withInput()->withErrors($validator->errors(), 'user.profile')));
    }

    public function processedValues()
    {
        return $this->blueprintFields->process()->values()
            ->only(array_keys($this->submittedValues))
            ->except(['email', 'password', 'groups', 'roles', 'super']);
    }

    private function valuesWithoutAssetFields($fields)
    {
        $assets = $fields->all()
            ->filter(fn ($field) => $field->fieldtype()->handle() === 'assets')
            ->keys()->all();

        return $this->except($assets);
    }
}

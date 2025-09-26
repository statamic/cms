<?php

namespace Statamic\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Traits\Localizable;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\ValidationException;
use Statamic\Facades\Site;
use Statamic\Facades\User;
use Statamic\Forms\Uploaders\AssetsUploader;
use Statamic\Rules\AllowedFile;
use Statamic\Rules\UniqueUserValue;
use Statamic\Support\Arr;

class UserRegisterRequest extends FormRequest
{
    use Localizable;

    public $blueprintFields;
    public $submittedValues;
    public $submittedAssets;

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
        return $this->blueprintFields
            ->addValues($this->submittedValues)
            ->process()
            ->values()
            ->only(array_keys($this->submittedValues))
            ->except(['email', 'groups', 'roles', 'super', 'password_confirmation']);
    }

    public function processedAssets()
    {
        return $this->blueprintFields
            ->addValues($this->uploadAssetFiles($this->blueprintFields))
            ->process()
            ->values()
            ->only(array_keys($this->submittedAssets));
    }

    public function validator()
    {
        $blueprint = User::blueprint();
        $blueprint->ensureField('password', ['display' => __('Password')]);
        $blueprint->ensureField('password_confirmation', ['display' => __('Password Confirmation')]);

        $fields = $blueprint->fields();
        $this->submittedValues = $this->valuesWithoutAssetFields($fields);
        $this->submittedAssets = $this->normalizeAssetsValues($fields);
        $this->blueprintFields = $fields;

        return $fields
            ->addValues(array_merge(
                $this->submittedValues,
                $this->submittedAssets,
            ))
            ->validator()
            ->withRules(array_merge([
                'email' => ['required', 'email', new UniqueUserValue],
                'password' => ['required', 'confirmed', Password::default()],
            ], $this->extraRules($fields)))
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

    private function normalizeAssetsValues($fields)
    {
        return $fields->all()
            ->filter(fn ($field) => $field->fieldtype()->handle() === 'assets' && $this->hasFile($field->handle()))
            ->map(fn ($field) => Arr::wrap($this->file($field->handle())))
            ->all();
    }

    protected function uploadAssetFiles($fields)
    {
        return $fields->all()
            ->filter(fn ($field) => $field->fieldtype()->handle() === 'assets' && $this->hasFile($field->handle()))
            ->map(fn ($field) => AssetsUploader::field($field)->upload($this->file($field->handle())))
            ->all();
    }

    private function extraRules($fields)
    {
        return $fields->all()
            ->filter(fn ($field) => $field->fieldtype()->handle() === 'assets')
            ->mapWithKeys(function ($field) {
                return [$field->handle().'.*' => ['file', new AllowedFile]];
            })
            ->all();
    }
}

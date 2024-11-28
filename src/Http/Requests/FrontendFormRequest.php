<?php

namespace Statamic\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Traits\Localizable;
use Illuminate\Validation\ValidationException;
use Statamic\Facades\Site;
use Statamic\Rules\AllowedFile;
use Statamic\Support\Arr;

class FrontendFormRequest extends FormRequest
{
    use Localizable;

    private $assets = [];
    private $cachedFields;

    /**
     * Get any assets in the request
     */
    public function assets(): array
    {
        return $this->assets;
    }

    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Optionally override the redirect url based on the presence of _error_redirect
     */
    protected function getRedirectUrl()
    {
        $url = $this->redirector->getUrlGenerator();

        if ($redirect = $this->input('_error_redirect')) {
            return $url->to($redirect);
        }

        return $url->previous();
    }

    public function validator()
    {
        $fields = $this->getFormFields();

        return $fields
            ->validator()
            ->withRules($this->extraRules($fields))
            ->validator();
    }

    protected function failedValidation(Validator $validator)
    {
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

        return parent::failedValidation($validator);
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

    private function getFormFields()
    {
        if ($this->cachedFields) {
            return $this->cachedFields;
        }

        $form = $this->route()->parameter('form');

        $this->errorBag = 'form.'.$form->handle();

        $fields = $form->blueprint()->fields();

        $this->assets = $this->normalizeAssetsValues($fields);

        $values = array_merge($this->all(), $this->assets);

        return $this->cachedFields = $fields->addValues($values);
    }

    private function normalizeAssetsValues($fields)
    {
        // The assets fieldtype is expecting an array, even for `max_files: 1`, but we don't want to force that on the front end.
        return $fields->all()
            ->filter(fn ($field) => in_array($field->fieldtype()->handle(), ['assets', 'files']) && $this->hasFile($field->handle()))
            ->map(fn ($field) => Arr::wrap($this->file($field->handle())))
            ->all();
    }

    public function validateResolved()
    {
        // If this was submitted from a front-end form, we want to use the appropriate language
        // for the translation messages. If there's no previous url, it was likely submitted
        // directly in a headless format. In that case, we'll just use the default lang.
        $site = ($previousUrl = $this->input('_original_url')) ? Site::findByUrl($previousUrl) : null;

        return $this->withLocale($site?->lang(), fn () => parent::validateResolved());
    }
}

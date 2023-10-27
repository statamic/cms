<?php

namespace Statamic\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Traits\Localizable;
use Illuminate\Validation\ValidationException;
use Statamic\Facades\Site;

class FrontendFormRequest extends FormRequest
{
    use Localizable;

    private $assets = [];
    private $cachedValidator;

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
     * Get the validation messages
     */
    public function messages(): array
    {
        $site = Site::findByUrl(URL::previous()) ?? Site::default();

        $messages = [];
        $this->withLocale($site->lang(), function () use (&$messages) {
            $messages = $this->getCustomValidator()
                ->validator()
                ->messages()
                ->getMessages();
        });

        return $messages;
    }

    /**
     * Get the validation attributes that apply to the request.
     */
    public function attributes()
    {
        $attributes = $this->getFormFields()->preProcessValidatables()->all()->reduce(function ($carry, $field) {
            return $carry->merge($field->validationAttributes());
        }, collect())->all();

        return $attributes;
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return $this->getCustomValidator()->rules();
    }

    /**
     * Optionally override the redirect url based on the presence of _error_redirect
     */
    protected function getRedirectUrl()
    {
        $url = $this->redirector->getUrlGenerator();

        if ($redirect = request()->input('_error_redirect')) {
            return $url->to($redirect);
        }

        return $url->previous();
    }

    protected function failedValidation(Validator $validator)
    {
        if (request()->ajax()) {
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

    protected function getValidatorInstance()
    {
        return $this->getCustomValidator()->validator();
    }

    public function extraRules($fields)
    {
        $assetFieldRules = $fields->all()
            ->filter(function ($field) {
                return $field->fieldtype()->handle() === 'assets';
            })
            ->mapWithKeys(function ($field) {
                return [$field->handle().'.*' => 'file'];
            })
            ->all();

        return $assetFieldRules;
    }

    private function getFormFields()
    {
        $request = request();

        $form = $this->route()->parameter('form');

        $this->errorBag = 'form.'.$form->handle();

        $fields = $form->blueprint()->fields();

        $this->assets = $this->normalizeAssetsValues($fields, $request);

        $values = array_merge($request->all(), $this->assets);

        $fields = $fields->addValues($values);

        return $fields;
    }

    private function getCustomValidator()
    {
        if (! $this->cachedValidator) {
            $fields = $this->getFormFields();
            $this->cachedValidator = $fields->validator()->withRules($this->extraRules($fields));
        }

        return $this->cachedValidator;
    }

    protected function normalizeAssetsValues($fields, $request)
    {
        // The assets fieldtype is expecting an array, even for `max_files: 1`, but we don't want to force that on the front end.
        return $fields->all()
            ->filter(function ($field) {
                return $field->fieldtype()->handle() === 'assets' && request()->hasFile($field->handle());
            })
            ->map(function ($field) use ($request) {
                return Arr::wrap($request->file($field->handle()));
            })
            ->all();
    }
}

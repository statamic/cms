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

        if ($redirect = request()->input('_error_redirect')) {
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

    private function extraRules($fields)
    {
        return $fields->all()
            ->filter(function ($field) {
                return $field->fieldtype()->handle() === 'assets';
            })
            ->mapWithKeys(function ($field) {
                return [$field->handle().'.*' => ['file', function ($attribute, $value, $fail) {
                    if (in_array(trim(strtolower($value->getClientOriginalExtension())), ['php', 'php3', 'php4', 'php5', 'php7', 'php8', 'phtml'])) {
                        $fail(__('validation.uploaded'));
                    }
                }]];
            })
            ->all();
    }

    private function getFormFields()
    {
        if ($this->cachedFields) {
            return $this->cachedFields;
        }

        $request = request();

        $form = $this->route()->parameter('form');

        $this->errorBag = 'form.'.$form->handle();

        $fields = $form->blueprint()->fields();

        $this->assets = $this->normalizeAssetsValues($fields, $request);

        $values = array_merge($request->all(), $this->assets);

        return $this->cachedFields = $fields->addValues($values);
    }

    private function normalizeAssetsValues($fields, $request)
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

    public function validateResolved()
    {
        $site = Site::findByUrl(URL::previous()) ?? Site::default();

        return $this->withLocale($site->lang(), function () {
            return parent::validateResolved();
        });
    }
}

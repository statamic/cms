<?php

namespace Statamic\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class FilteredRequest extends FormRequest
{
    protected function prepareForValidation()
    {
        if ($filters = $this->filters) {
            $this->replace([
                'filters' => collect(json_decode(base64_decode($filters), true))
            ]);
        }
    }

    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            //
        ];
    }
}

<?php

namespace Statamic\Fields\Fieldtypes;

use Illuminate\Support\Arr;
use Statamic\Fields\Fieldtype;

class Relationship extends Fieldtype
{
    protected $categories = ['relationship'];

    protected $configFields = [
        'max_items' => ['type' => 'integer'],
        'collections' => ['type' => 'list'],
    ];

    public function preProcess($data)
    {
        return Arr::wrap($data);
    }

    public function rules(): array
    {
        $rules = ['array'];

        if ($max = $this->config('max_items')) {
            $rules[] = 'max:' . $max;
        }

        return $rules;
    }
}

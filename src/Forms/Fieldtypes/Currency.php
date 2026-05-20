<?php

namespace Statamic\Forms\Fieldtypes;

use Statamic\Facades\Dictionary;
use Statamic\Forms\Fields\FormFieldtype;
use Statamic\Support\Arr;

class Currency extends FormFieldtype
{
    protected static $fieldtype = 'integer';
    protected $description = 'Collects a monetary amount.';
    protected $icon = 'currency';
    protected $categories = ['number'];

    public function configFieldItems(): array
    {
        return [
            'currency' => [
                'display' => __('Currency'),
                'type' => 'dictionary',
                'dictionary' => 'currencies',
                'validate' => 'required',
                'max_items' => 1,
            ],
        ];
    }

    public function toFieldArray(): array
    {
        $currency = Dictionary::find('currencies')->get($this->config('currency'));

        return [
            'type' => 'integer',
            'prepend' => Arr::get($currency->extra(), 'symbol'),
            ...Arr::except($this->config(), ['type', 'currency']),
        ];
    }

    public function example(): ?array
    {
        return [
            'config' => [
                'display' => 'How much do you usually spend on coffee per week?',
                'currency' => 'USD',
            ],
            'value' => 30,
        ];
    }
}

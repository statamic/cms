<?php

namespace Statamic\Fieldtypes;

use Statamic\Facades\GraphQL;
use Statamic\Fields\Fieldtype;

class Integer extends Fieldtype
{
    protected $categories = ['number'];
    protected $selectableInForms = true;

    protected function configFieldItems(): array
    {
        return [
            'default' => [
                'display' => __('Default Value'),
                'instructions' => __('statamic::messages.fields_default_instructions'),
                'type' => 'text',
                'width' => 50,
            ],
            'prepend' => [
                'display' => __('Prepend'),
                'instructions' => __('statamic::fieldtypes.text.config.prepend'),
                'type' => 'text',
                'width' => 50,
            ],
            'append' => [
                'display' => __('Append'),
                'instructions' => __('statamic::fieldtypes.text.config.append'),
                'type' => 'text',
                'width' => 50,
            ],
        ];
    }

    public function preProcess($data)
    {
        if ($data === null) {
            return null;
        }

        return (int) $data;
    }

    public function preProcessConfig($data)
    {
        return (int) $data;
    }

    public function process($data)
    {
        if ($data === null || $data === '') {
            return null;
        }

        return (int) $data;
    }

    public function toGqlType()
    {
        return GraphQL::int();
    }

    public function rules(): array
    {
        $rules = ['integer'];

        if ($min = $this->config('min')) {
            $rules[] = 'min:'.$min;
        }

        return $rules;
    }
}

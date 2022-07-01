<?php

namespace Statamic\Fieldtypes;

use Statamic\Facades\GraphQL;
use Statamic\Fields\Fieldtype;

class Toggle extends Fieldtype
{
    protected $categories = ['controls'];
    protected $selectableInForms = true;
    protected $defaultValue = false;

    protected function configFieldItems(): array
    {
        return [
            'inline_label' => [
                'display' => __('Inline Label'),
                'instructions' => __('statamic::fieldtypes.toggle.config.inline_label'),
                'type' => 'text',
                'default' => '',
            ],
            'default' => [
                'display' => __('Default Value'),
                'instructions' => __('statamic::messages.fields_default_instructions'),
                'type' => 'toggle',
                'width' => 50,
            ],
        ];
    }

    public function preProcess($data)
    {
        return (bool) $data;
    }

    public function process($data)
    {
        return (bool) $data;
    }

    public function augment($data)
    {
        return (bool) $data;
    }

    public function toGqlType()
    {
        return GraphQL::boolean();
    }
}

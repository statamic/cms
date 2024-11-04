<?php

namespace Statamic\Fieldtypes;

use Statamic\Facades\GraphQL;
use Statamic\Fields\Fieldtype;
use Statamic\Query\Scopes\Filters\Fields\Toggle as ToggleFilter;

class Toggle extends Fieldtype
{
    protected $categories = ['controls'];
    protected $keywords = ['checkbox', 'bool', 'boolean'];
    protected $selectableInForms = true;
    protected $defaultValue = false;

    protected function configFieldItems(): array
    {
        return [
            [
                'display' => __('Appearance & Behavior'),
                'fields' => [
                    'inline_label' => [
                        'display' => __('Inline Label'),
                        'instructions' => __('statamic::fieldtypes.toggle.config.inline_label'),
                        'type' => 'text',
                        'default' => '',
                    ],
                    'inline_label_when_true' => [
                        'display' => __('Inline Label when True'),
                        'instructions' => __('statamic::fieldtypes.toggle.config.inline_label_when_true'),
                        'type' => 'text',
                        'default' => '',
                    ],
                    'default' => [
                        'display' => __('Default Value'),
                        'instructions' => __('statamic::messages.fields_default_instructions'),
                        'type' => 'toggle',
                    ],
                ],
            ],
        ];
    }

    public function preProcess($data)
    {
        return (bool) $data;
    }

    public function process($data)
    {
        if (is_null($data)) {
            return null;
        }

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

    public function filter()
    {
        return new ToggleFilter($this);
    }
}

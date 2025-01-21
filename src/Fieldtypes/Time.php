<?php

namespace Statamic\Fieldtypes;

use Statamic\Fields\Fieldtype;
use Statamic\Rules\TimeFieldtype as ValidationRule;

class Time extends Fieldtype
{
    protected $categories = ['special'];

    protected function configFieldItems(): array
    {
        return [
            [
                'display' => __('Appearance & Behavior'),
                'fields' => [
                    'seconds_enabled' => [
                        'display' => __('Show Seconds'),
                        'instructions' => __('statamic::fieldtypes.time.config.seconds_enabled'),
                        'type' => 'toggle',
                        'default' => false,
                    ],
                    'default' => [
                        'display' => __('Default Value'),
                        'instructions' => __('statamic::messages.fields_default_instructions'),
                        'type' => 'text',
                    ],
                ],
            ],
        ];
    }

    public function rules(): array
    {
        return [new ValidationRule($this)];
    }
}

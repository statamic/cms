<?php

namespace Statamic\Fieldtypes;

use Illuminate\Support\Facades\Date;
use Statamic\Fields\Fieldtype;
use Statamic\Rules\TimeFieldtype as ValidationRule;

class Time extends Fieldtype
{
    protected $categories = ['special'];

    protected function configFieldItems(): array
    {
        return [
            [
                'display' => __('Appearance'),
                'fields' => [
                    'seconds_enabled' => [
                        'display' => __('Show Seconds'),
                        'instructions' => __('statamic::fieldtypes.time.config.seconds_enabled'),
                        'type' => 'toggle',
                        'default' => false,
                    ],
                ],
            ],
            [
                'display' => __('Data & Format'),
                'fields' => [
                    'default' => [
                        'display' => __('Default Value'),
                        'instructions' => __('statamic::messages.fields_default_instructions'),
                        'type' => 'text',
                    ],
                    'augment_format' => [
                        'display' => __('Augment Format'),
                        'instructions' => __('statamic::fieldtypes.time.config.augment_format'),
                        'type' => 'text',
                    ],
                ],
            ],
        ];
    }

    public function augment($value)
    {
        if (! $value || ! $this->config('augment_format')) {
            return $value;
        }

        return Date::parse($value)->format($this->config('augment_format'));
    }

    public function rules(): array
    {
        return [new ValidationRule($this)];
    }
}

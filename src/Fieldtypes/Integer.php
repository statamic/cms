<?php

namespace Statamic\Fieldtypes;

use Statamic\Facades\GraphQL;
use Statamic\Fields\Fieldtype;
use Statamic\Query\Scopes\Filters\Fields\Integer as IntegerFilter;

class Integer extends Fieldtype
{
    protected $categories = ['number'];
	
    protected $selectableInForms = true;

    protected function configFieldItems(): array
    {
        return [
            [
                'display' => __('Behavior'),
                'fields' => [
                    'placeholder' => [
                        'display' => __('Placeholder'),
                        'instructions' => __('statamic::fieldtypes.text.config.placeholder'),
                        'type' => 'text',
                    ],
                    'min' => [
                        'display' => __('Minimum Value'),
                        'instructions' => __('The minimum allowed value.'),
                        'type' => 'integer',
                    ],
                    'max' => [
                        'display' => __('Maximum Value'),
                        'instructions' => __('The maximum allowed value.'),
                        'type' => 'integer',
                    ],
                    'step' => [
                        'display' => __('Step'),
                        'instructions' => __('The interval between valid numbers.'),
                        'type' => 'integer',
                    ],
                    'default' => [
                        'display' => __('Default Value'),
                        'instructions' => __('statamic::messages.fields_default_instructions'),
                        'type' => 'text',
                    ],
                ],
            ],
            [
                'display' => __('Appearance'),
                'fields' => [
                    'prepend' => [
                        'display' => __('Prepend'),
                        'instructions' => __('statamic::fieldtypes.text.config.prepend'),
                        'type' => 'text',
                    ],
                    'append' => [
                        'display' => __('Append'),
                        'instructions' => __('statamic::fieldtypes.text.config.append'),
                        'type' => 'text',
                    ],

                ],
            ],
        ];
    }

    public function viewData($data)
    {
        return [
            'min' => $this->config('min'),
            'max' => $this->config('max'),
            'step' => $this->config('step'),
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

        if ($max = $this->config('max')) {
            $rules[] = 'max:'.$max;
        }

        if ($step = $this->config('step')) {
            $rules[] = 'multiple_of:'.$step;
        }

        return $rules;
    }

    public function filter()
    {
        return new IntegerFilter($this);
    }
}

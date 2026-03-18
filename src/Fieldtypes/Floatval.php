<?php

namespace Statamic\Fieldtypes;

use Statamic\Facades\GraphQL;
use Statamic\Fields\Fieldtype;
use Statamic\Query\Scopes\Filters\Fields\Floatval as FloatFilter;

class Floatval extends Fieldtype
{
    protected $categories = ['number'];
    protected $rules = ['numeric'];
    protected static $handle = 'float';

    protected function configFieldItems(): array
    {
        return [
            'min' => [
                'display' => __('Min'),
                'instructions' => __('statamic::fieldtypes.integer.config.min'),
                'type' => 'float',
            ],
            'max' => [
                'display' => __('Max'),
                'instructions' => __('statamic::fieldtypes.integer.config.max'),
                'type' => 'float',
            ],
            'step' => [
                'display' => __('Step'),
                'instructions' => __('statamic::fieldtypes.integer.config.step'),
                'type' => 'float',
            ],
            'default' => [
                'display' => __('Default Value'),
                'instructions' => __('statamic::messages.fields_default_instructions'),
                'type' => 'text',
            ],
            'prepend' => [
                'display' => __('Prepend'),
                'instructions' => __('statamic::fieldtypes.text.config.prepend'),
                'type' => 'text',
                'width' => '50',
            ],
            'append' => [
                'display' => __('Append'),
                'instructions' => __('statamic::fieldtypes.text.config.append'),
                'type' => 'text',
                'width' => '50',
            ],
        ];
    }

    /**
     * Pre-process the data before it gets sent to the publish page.
     *
     * @param  mixed  $data
     * @return array|mixed
     */
    public function preProcess($data)
    {
        if ($data === null) {
            return;
        }

        return floatval($data);
    }

    public function process($data)
    {
        if ($data === null || $data === '') {
            return;
        }

        return floatval($data);
    }

    public function filter()
    {
        return new FloatFilter($this);
    }

    public function toGqlType()
    {
        return GraphQL::type(GraphQL::float());
    }

    public function rules(): array
    {
        $rules = ['numeric'];

        if ($min = $this->config('min')) {
            $rules[] = 'min:'.$min;
        }

        if ($max = $this->config('max')) {
            $rules[] = 'max:'.$max;
        }

        return $rules;
    }
}

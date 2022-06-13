<?php

namespace Statamic\Fieldtypes;

use Statamic\Fields\Fieldtype;

class Floatval extends Fieldtype
{
    protected $categories = ['number'];
    protected $icon = 'float';
    protected $rules = ['numeric'];
    protected static $handle = 'float';

    protected function configFieldItems(): array
    {
        return [
            'default' => [
                'display' => __('Default Value'),
                'instructions' => __('statamic::messages.fields_default_instructions'),
                'type' => 'text',
                'width' => 50,
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

    public function preProcessConfig($data)
    {
        return floatval($data);
    }

    public function process($data)
    {
        if ($data === null || $data === '') {
            return;
        }

        return floatval($data);
    }
}

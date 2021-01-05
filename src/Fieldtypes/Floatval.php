<?php

namespace Statamic\Fieldtypes;

use Statamic\Fields\Fieldtype;

class Floatval extends Fieldtype
{
    protected $icon = 'integer';
    protected $rules = ['numeric'];
    protected static $title = 'Float';
    protected static $handle = 'float';

    /**
     * Pre-process the data before it gets sent to the publish page.
     *
     * @param mixed $data
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

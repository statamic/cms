<?php

namespace Statamic\Addons\Arr;

use Statamic\Extend\Fieldtype;

class ArrFieldtype extends Fieldtype
{
    protected $snake_name = 'array';

    public function blank()
    {
        return [];
    }

    public function preProcess($data)
    {
        if ($this->keyed()) {
            $processed = [];
            foreach (format_input_options($this->getFieldConfig('keys')) as $formatted) {
                $value = $formatted['value'];
                $processed[$value] = array_get($data, $value);
            }
            $data = $processed;
        } else {
            $data = format_input_options($data);
        }

        return $data;
    }

    public function process($data)
    {
        // The keyed version is fine as-is.
        if ($this->keyed()) {
            return $data;
        }

        $result = [];

        if (! is_array($data)) {
            return $data;
        }

        foreach ($data as $i => $arr) {
            $key = $arr['value'];
            $value = $arr['text'];

            $result[$key] = $value;
        }

        return $result;
    }

    private function keyed()
    {
        return (bool) $this->getFieldConfig('keys');
    }
}

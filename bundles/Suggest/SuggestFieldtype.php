<?php

namespace Statamic\Addons\Suggest;

use Statamic\Exceptions\FatalException;
use Statamic\Extend\Fieldtype;

class SuggestFieldtype extends Fieldtype
{
    public function blank()
    {
        return [];
    }

    public function preProcess($data)
    {
        if ($this->getFieldConfig('max_items') === 1) {
            $data = [$data];
        }

        return $data;
    }

    public function process($data)
    {
        if ($this->getFieldConfig('max_items') === 1) {
            $data = reset($data);
        }

        return $data;
    }
}

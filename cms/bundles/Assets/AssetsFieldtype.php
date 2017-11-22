<?php

namespace Statamic\Addons\Assets;

use Statamic\API\Helper;
use Statamic\Extend\Fieldtype;

class AssetsFieldtype extends Fieldtype
{
    public function canBeValidated()
    {
        return false;
    }

    public function canHaveDefault()
    {
        return false;
    }

    public function blank()
    {
        return [];
    }

    public function preProcess($data)
    {
        $max_files = (int) $this->getFieldConfig('max_files');

        if ($max_files === 1 && empty($data)) {
            return $data;
        }

        return Helper::ensureArray($data);
    }

    public function process($data)
    {
        $max_files = (int) $this->getFieldConfig('max_files');

        if ($max_files === 1) {
            return array_get($data, 0);
        }

        return $data;
    }
}

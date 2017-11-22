<?php

namespace Statamic\Addons\Lists;

use Statamic\API\Helper;
use Statamic\Extend\Fieldtype;

class ListsFieldtype extends Fieldtype
{
    protected $snake_name = 'list';

    public function preProcess($data)
    {
        if (is_null($data)) {
            return [];
        }

        return Helper::ensureArray($data);
    }
}

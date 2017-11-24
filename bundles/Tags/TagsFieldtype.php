<?php

namespace Statamic\Addons\Tags;

use Statamic\Extend\Fieldtype;

class TagsFieldtype extends Fieldtype
{
    public function preProcess($data)
    {
        return ($data) ? $data : [];
    }
}

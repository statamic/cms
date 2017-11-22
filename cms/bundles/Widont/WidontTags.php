<?php

namespace Statamic\Addons\Widont;

use Statamic\API\Helper;
use Statamic\Extend\Tags;

class WidontTags extends Tags
{
    public function index()
    {
        return Helper::widont($this->content);
    }
}

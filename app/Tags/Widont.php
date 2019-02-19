<?php

namespace Statamic\Tags;

use Statamic\Tags\Tags;
use Statamic\API\Helper;

class Widont extends Tags
{
    public function index()
    {
        return Helper::widont($this->content);
    }
}

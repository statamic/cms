<?php

namespace Statamic\Tags;

use Statamic\API\Helper;
use Statamic\Tags\Tags;

class Widont extends Tags
{
    public function index()
    {
        return Helper::widont($this->content);
    }
}

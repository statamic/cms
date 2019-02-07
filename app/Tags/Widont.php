<?php

namespace Statamic\Tags;

use Statamic\API\Helper;
use Statamic\Tags\Tag;

class Widont extends Tag
{
    public function index()
    {
        return Helper::widont($this->content);
    }
}

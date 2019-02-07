<?php

namespace Statamic\Tags;

use Statamic\Tags\Tag;
use Statamic\Exceptions\UrlNotFoundException;

class NotFound extends Tag
{
    public function index()
    {
        throw new UrlNotFoundException;
    }
}

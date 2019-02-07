<?php

namespace Statamic\Tags;

use Statamic\Tags\Tags;
use Statamic\Exceptions\UrlNotFoundException;

class NotFound extends Tags
{
    public function index()
    {
        throw new UrlNotFoundException;
    }
}

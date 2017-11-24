<?php

namespace Statamic\Addons\NotFound;

use Statamic\Extend\Tags;
use Statamic\Exceptions\UrlNotFoundException;

class NotFoundTags extends Tags
{
    public function index()
    {
        throw new UrlNotFoundException;
    }
}

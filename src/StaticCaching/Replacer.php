<?php

namespace Statamic\StaticCaching;

use Symfony\Component\HttpFoundation\Response;

interface Replacer
{
    public function prepareForCache(Response $response);

    public function replaceInResponse(Response $response);
}

<?php

namespace Statamic\StaticCaching;

use Illuminate\Http\Response;

interface Replacer
{
    public function prepareResponseToCache(Response $response, Response $initial);

    public function replaceInCachedResponse(Response $response);
}

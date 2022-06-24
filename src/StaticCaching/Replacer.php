<?php

namespace Statamic\StaticCaching;

 use Symfony\Component\HttpFoundation\Response;

interface Replacer
{
    public function prepareResponseToCache(Response $response, Response $initial);

    public function replaceInCachedResponse(Response $response);
}

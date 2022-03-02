<?php

namespace Statamic\StaticCaching\Replacers;

use Statamic\StaticCaching\Replacer;
use Symfony\Component\HttpFoundation\Response;

class CsrfTokenReplacer implements Replacer
{
    const REPLACEMENT = '<statamic-cache-crsf-token>';

    public function prepareForCache(Response $response)
    {
        if (! $response->getContent()) { return; }

        $response->setContent(str_replace(
            csrf_token(),
            self::REPLACEMENT,
            $response->getContent()
        ));
    }

    public function replaceInResponse(Response $response)
    {
        if (! $response->getContent()) { return; }

        $response->setContent(str_replace(
            self::REPLACEMENT,
            csrf_token(),
            $response->getContent()
        ));
    }
}
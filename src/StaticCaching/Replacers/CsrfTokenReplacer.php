<?php

namespace Statamic\StaticCaching\Replacers;

use Illuminate\Http\Response;
use Statamic\StaticCaching\Replacer;

class CsrfTokenReplacer implements Replacer
{
    const REPLACEMENT = 'STATAMIC_CSRF_TOKEN';

    public function prepareResponseToCache(Response $response, Response $initial)
    {
        if (! $response->getContent()) {
            return;
        }

        $response->setContent(str_replace(
            csrf_token(),
            self::REPLACEMENT,
            $response->getContent()
        ));
    }

    public function replaceInCachedResponse(Response $response)
    {
        if (! $response->getContent()) {
            return;
        }

        $response->setContent(str_replace(
            self::REPLACEMENT,
            csrf_token(),
            $response->getContent()
        ));
    }
}

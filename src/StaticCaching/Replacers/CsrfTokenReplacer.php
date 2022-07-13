<?php

namespace Statamic\StaticCaching\Replacers;

use Illuminate\Http\Response;
use Statamic\Facades\StaticCache;
use Statamic\StaticCaching\Replacer;

class CsrfTokenReplacer implements Replacer
{
    const REPLACEMENT = 'STATAMIC_CSRF_TOKEN';

    public function prepareResponseToCache(Response $response, Response $initial)
    {
        if (! $content = $response->getContent()) {
            return;
        }

        if (! $token = csrf_token()) {
            return;
        }

        if (! str_contains($content, $token)) {
            return;
        }

        StaticCache::includeJs();

        $response->setContent(str_replace(
            $token,
            self::REPLACEMENT,
            $content
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

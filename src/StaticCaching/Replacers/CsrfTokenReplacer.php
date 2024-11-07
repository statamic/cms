<?php

namespace Statamic\StaticCaching\Replacers;

use Illuminate\Http\Response;
use Statamic\Facades\StaticCache;
use Statamic\StaticCaching\Cacher;
use Statamic\StaticCaching\Cachers\FileCacher;
use Statamic\StaticCaching\Replacer;
use Statamic\Support\Str;

class CsrfTokenReplacer implements Replacer
{
    const REPLACEMENT = 'STATAMIC_CSRF_TOKEN';

    public function prepareResponseToCache(Response $response, Response $initial)
    {
        $this->replaceInResponse($response);

        $this->modifyFullMeasureResponse($response);
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

    private function replaceInResponse(Response $response)
    {
        if (! $content = $response->getContent()) {
            return;
        }

        if (! $token = csrf_token()) {
            return;
        }

        if (! Str::contains($content, $token)) {
            return;
        }

        StaticCache::includeJs();

        $response->setContent(str_replace(
            $token,
            self::REPLACEMENT,
            $content
        ));
    }

    private function modifyFullMeasureResponse(Response $response)
    {
        $cacher = app(Cacher::class);

        if (! $cacher instanceof FileCacher) {
            return;
        }

        if (! $cacher->shouldOutputJs()) {
            return;
        }

        if (! $cacher->shouldOutputDecoupledScripts()) {
            return;
        }

        $contents = $response->getContent();

        $insertBefore = collect([
            Str::position($contents, '<link'),
            Str::position($contents, '<script'),
            Str::position($contents, '</head>'),
        ])->filter()->min();

        $js = "<script type=\"text/javascript\">{$cacher->getCsrfTokenJs()}</script>";

        $contents = Str::substrReplace($contents, $js, $insertBefore, 0);

        $response->setContent($contents);
    }
}

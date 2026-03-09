<?php

namespace Statamic\StaticCaching\Replacers;

use Illuminate\Http\Response;
use Statamic\Facades\StaticCache;
use Statamic\StaticCaching\Cacher;
use Statamic\StaticCaching\Cachers\FileCacher;
use Statamic\StaticCaching\NoCache\Session;
use Statamic\StaticCaching\Replacer;

class NoCacheReplacer implements Replacer
{
    const PATTERN = '/<span class="nocache" data-nocache="([\w\d]+)">NOCACHE_PLACEHOLDER<\/span>/';

    private $session;

    public function __construct(Session $session)
    {
        $this->session = $session;
    }

    public function prepareResponseToCache(Response $responseToBeCached, Response $initialResponse)
    {
        if (app(Cacher::class) instanceof FileCacher) {
            $this->includeJs($initialResponse);
            $this->modifyFullMeasureResponse($initialResponse);
        } else {
            $this->replaceInResponse($initialResponse);
        }

        $this->modifyFullMeasureResponse($responseToBeCached);
    }

    public function replaceInCachedResponse(Response $response)
    {
        $this->replaceInResponse($response);
    }

    private function replaceInResponse(Response $response)
    {
        if (! $content = $response->getContent()) {
            return;
        }

        $this->includeJs($response);

        $response->setContent($this->replace($content));
    }

    private function includeJs(Response $response)
    {
        if (! $content = $response->getContent()) {
            return;
        }

        if (preg_match(self::PATTERN, $content)) {
            StaticCache::includeJs();
        }
    }

    public function replace(string $content)
    {
        if (preg_match(self::PATTERN, $content)) {
            $this->session->restore();
        }

        while (preg_match(self::PATTERN, $content)) {
            $content = $this->performReplacement($content);
        }

        return $content;
    }

    private function performReplacement(string $content)
    {
        return preg_replace_callback(self::PATTERN, function ($matches) {
            if (! $region = $matches[1] ?? null) {
                return '';
            }

            return $this->session->region($region)->render();
        }, $content);
    }

    private function modifyFullMeasureResponse(Response $response)
    {
        $cacher = app(Cacher::class);

        if (! $cacher instanceof FileCacher) {
            return;
        }

        $contents = $response->getContent();

        if ($cacher->shouldOutputJs()) {
            $js = $cacher->getNocacheJs();
            $contents = str_replace('</body>', '<script>'.$js.'</script></body>', $contents);
        }

        $contents = str_replace('NOCACHE_PLACEHOLDER', $cacher->getNocachePlaceholder(), $contents);

        $response->setContent($contents);
    }
}

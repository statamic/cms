<?php

namespace Statamic\StaticCaching\Replacers;

use Illuminate\Http\Response;
use Illuminate\Support\Str;
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
        $this->replaceInResponse($initialResponse);

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

        if (preg_match(self::PATTERN, $content)) {
            $this->session->restore();

            StaticCache::includeJs();
        }

        $response->setContent($this->replace($content));
    }

    public function replace(string $content)
    {
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
            $contents = match ($pos = $this->insertPosition()) {
                'head' => $this->insertJsInHead($contents, $cacher),
                'body' => $this->insertJsInBody($contents, $cacher),
                default => throw new \Exception('Invalid nocache js insert position ['.$pos.']'),
            };
        }

        $contents = str_replace('NOCACHE_PLACEHOLDER', $cacher->getNocachePlaceholder(), $contents);

        $response->setContent($contents);
    }

    private function insertPosition()
    {
        return config('statamic.static_caching.nocache_js_position', 'body');
    }

    private function insertJsInHead($contents, $cacher)
    {
        $insertBefore = collect([
            Str::position($contents, '<link'),
            Str::position($contents, '<script'),
            Str::position($contents, '</head>'),
        ])->filter()->min();

        $js = "<script type=\"text/javascript\">{$cacher->getNocacheJs()}</script>";

        return Str::substrReplace($contents, $js, $insertBefore, 0);
    }

    private function insertJsInBody($contents, $cacher)
    {
        $js = $cacher->getNocacheJs();

        return str_replace('</body>', '<script type="text/javascript">'.$js.'</script></body>', $contents);
    }
}

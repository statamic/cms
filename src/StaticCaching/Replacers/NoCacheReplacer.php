<?php

namespace Statamic\StaticCaching\Replacers;

use Statamic\StaticCaching\NoCache\CacheSession;
use Statamic\StaticCaching\Replacer;
use Symfony\Component\HttpFoundation\Response;

class NoCacheReplacer implements Replacer
{
    const PATTERN = '/<no_cache_section:([\w\d]+)>/';

    private $session;

    public function __construct(CacheSession $session)
    {
        $this->session = $session;
    }

    public function prepareResponseToCache(Response $cached, Response $response)
    {
        if (! $content = $response->getContent()) {
            return;
        }

        $content = $this->replace($content);

        $response->setContent($content);
    }

    public function replaceInCachedResponse(Response $response)
    {
        if (! $content = $response->getContent()) {
            return;
        }

        $this->session->restore();

        $response->setContent($this->replace($content));
    }

    private function replace(string $content)
    {
        while (preg_match(self::PATTERN, $content)) {
            $content = $this->performReplacement($content);
        }

        return $content;
    }

    private function performReplacement(string $content)
    {
        return preg_replace_callback(self::PATTERN, function ($matches) {
            if (! $section = $matches[1] ?? null) {
                return '';
            }

            return $this->session->getView($section)->render();
        }, $content);
    }
}

<?php

namespace Statamic\StaticCaching\Replacers;

use Statamic\StaticCaching\NoCache\CacheSession;
use Statamic\StaticCaching\Replacer;
use Symfony\Component\HttpFoundation\Response;

class NoCacheReplacer implements Replacer
{
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
        return preg_replace_callback('/<no_cache_section:([\w\d]+)>/', function ($matches) {
            if (! $section = $matches[1] ?? null) {
                return '';
            }

            return $this->session->getView($section)->render();
        }, $content);
    }
}

<?php

namespace Statamic\StaticCaching\Replacers;

use Statamic\StaticCaching\NoCache\NoCacheManager;
use Statamic\StaticCaching\Replacer;
use Symfony\Component\HttpFoundation\Response;

class NoCacheReplacer implements Replacer
{
    private $manager;

    public function __construct(NoCacheManager $manager)
    {
        $this->manager = $manager;
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

        $this->manager->session()->restore();

        $response->setContent($this->replace($content));
    }

    private function replace(string $content)
    {
        return preg_replace_callback('/<no_cache_section:([\w\d]+)>/', function ($matches) {
            if (! $section = $matches[1] ?? null) {
                return '';
            }

            return $this->manager->session()->getView($section)->render();
        }, $content);
    }
}

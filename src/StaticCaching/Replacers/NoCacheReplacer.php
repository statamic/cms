<?php

namespace Statamic\StaticCaching\Replacers;

use Statamic\Facades\File;
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
        view()->addNamespace('nocache', $this->viewPath());
        File::makeDirectory($this->viewPath());

        return preg_replace_callback('/<no_cache_section:([\w\d]+)>/', function ($matches) {
            if (! $section = $matches[1] ?? null) {
                return '';
            }

            $session = $this->manager->session();

            $view = $session->getView($section);

            $this->createTemporaryView($section, $view['engine'], $view['view']);

            return view('nocache::'.$section, $session->getViewData($section))->render();
        }, $content);
    }

    private function viewPath()
    {
        return config('view.compiled').'/nocache';
    }

    private function createTemporaryView($section, $engine, $contents)
    {
        $viewPath = sprintf('%s/%s.%s', $this->viewPath(), $section, $engine);

        if (File::exists($viewPath)) {
            return;
        }

        File::put($viewPath, $contents);
    }
}

<?php

namespace Statamic\StaticCaching\NoCache;

use Facades\Statamic\View\Cascade;
use Illuminate\Support\Facades\Cache;
use Statamic\Facades\Data;

class CacheSession
{
    protected $ignoreVars = [
        '__env', 'app', 'errors',
    ];
    protected $cascade = [];
    protected $sections = [];
    protected $url;

    public function __construct($url)
    {
        $this->url = $url;
    }

    public function getUrl()
    {
        return $this->url;
    }

    public function getSections()
    {
        return $this->sections;
    }

    public function pushSection($contents, $context, $extension)
    {
        foreach ($this->ignoreVars as $varName) {
            unset($context[$varName]);
        }

        $contents = trim($contents);
        $key = sha1($contents);

        $this->sections[$key] = [
            'type' => 'string',
            'contents' => $contents,
            'extension' => $extension,
            'context' => $this->filterContext($context),
        ];

        return sprintf('<no_cache_section:%s>', $key);
    }

    public function pushView($view, $context)
    {
        foreach ($this->ignoreVars as $varName) {
            unset($context[$varName]);
        }

        $key = str_random(32);

        $this->sections[$key] = [
            'type' => 'view',
            'view' => $view,
            'context' => $this->filterContext($context)
        ];

        return sprintf('<no_cache_section:%s>', $key);
    }

    public function getContext($region)
    {
        return $this->sections[$region]['context'];
    }

    public function getView($region)
    {
        $section = $this->sections[$region];

        $data = $this->getViewData($region);

        if ($section['type'] === 'string') {
            return new StringView($region, $section['contents'], $section['extension'], $data);
        } elseif ($section['type'] === 'view') {
            return new ViewView($section['view'], $data);
        }

        throw new \Exception('Unknown section type.');
    }

    public function getCascade()
    {
        return $this->cascade;
    }

    public function setCascade(array $cascade)
    {
        $this->cascade = $cascade;

        return $this;
    }

    private function filterContext(array $context): array
    {
        return $this->arrayRecursiveDiff($context, $this->cascade);
    }

    private function arrayRecursiveDiff($a, $b)
    {
        $data = [];

        foreach ($a as $aKey => $aValue) {
            if (! is_object($aKey) && is_array($b) && array_key_exists($aKey, $b)) {
                if (is_array($aValue)) {
                    $aRecursiveDiff = $this->arrayRecursiveDiff($aValue, $b[$aKey]);

                    if (! empty($aRecursiveDiff)) {
                        $data[$aKey] = $aRecursiveDiff;
                    }
                } else {
                    if ($aValue != $b[$aKey]) {
                        $data[$aKey] = $aValue;
                    }
                }
            } else {
                $data[$aKey] = $aValue;
            }
        }

        return $data;
    }

    public function getViewData($section): array
    {
        return array_merge($this->cascade, $this->getContext($section));
    }

    public function reset()
    {
        $this->contexts = [];
        $this->sections = [];
        $this->cascade = [];
    }

    public function write()
    {
        if (empty($this->sections)) {
            return;
        }

        Cache::forever('nocache::session.'.md5($this->url), [
            'sections' => $this->sections,
        ]);
    }

    public function restore()
    {
        $session = Cache::get('nocache::session.'.md5($this->url));

        $this->sections = array_merge($this->sections, $session['sections'] ?? []);
        $this->cascade = $this->restoreCascade();

        return $this;
    }

    private function restoreCascade()
    {
        return Cascade::instance()
            ->withContent(Data::findByRequestUrl($this->url))
            ->hydrate()
            ->toArray();
    }
}

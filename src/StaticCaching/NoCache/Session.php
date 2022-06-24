<?php

namespace Statamic\StaticCaching\NoCache;

use Facades\Statamic\View\Cascade;
use Illuminate\Support\Facades\Cache;
use Statamic\Facades\Data;

class Session
{
    protected $ignoreVars = [
        '__env', 'app', 'errors',
    ];
    protected $cascade = [];
    protected $regions = [];
    protected $url;

    public function __construct($url)
    {
        $this->url = $url;
    }

    public function getUrl()
    {
        return $this->url;
    }

    public function setUrl(string $url)
    {
        $this->url = $url;

        return $this;
    }

    public function getRegions()
    {
        return $this->regions;
    }

    public function pushRegion($contents, $context, $extension)
    {
        foreach ($this->ignoreVars as $varName) {
            unset($context[$varName]);
        }

        $contents = trim($contents);
        $key = sha1($contents.str_random());

        $this->regions[$key] = [
            'type' => 'string',
            'contents' => $contents,
            'extension' => $extension,
            'context' => $this->filterContext($context),
        ];

        return $key;
    }

    public function pushView($view, $context)
    {
        foreach ($this->ignoreVars as $varName) {
            unset($context[$varName]);
        }

        $key = str_random(32);

        $this->regions[$key] = [
            'type' => 'view',
            'view' => $view,
            'context' => $this->filterContext($context),
        ];

        return $key;
    }

    public function getContext($region)
    {
        return $this->regions[$region]['context'];
    }

    public function getFragment($key): Fragment
    {
        $region = $this->regions[$key];

        $data = $this->getFragmentData($key);

        if ($region['type'] === 'string') {
            return new StringFragment($key, $region['contents'], $region['extension'], $data);
        } elseif ($region['type'] === 'view') {
            return new ViewFragment($region['view'], $data);
        }

        throw new \Exception('Unknown region type.');
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

    public function getFragmentData($region): array
    {
        return array_merge($this->cascade, $this->getContext($region));
    }

    public function reset()
    {
        $this->contexts = [];
        $this->regions = [];
        $this->cascade = [];
    }

    public function write()
    {
        if (empty($this->regions)) {
            return;
        }

        Cache::forever('nocache::session.'.md5($this->url), [
            'regions' => $this->regions,
        ]);
    }

    public function restore()
    {
        $session = Cache::get('nocache::session.'.md5($this->url));

        $this->regions = array_merge($this->regions, $session['regions'] ?? []);
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

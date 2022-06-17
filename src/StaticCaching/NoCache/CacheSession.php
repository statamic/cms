<?php

namespace Statamic\StaticCaching\NoCache;

use Illuminate\Support\Facades\Cache;

class CacheSession
{
    protected $ignoreVars = [
        '__env', 'app', 'errors',
    ];
    protected $sections = [];
    protected $contexts = [];
    protected $url;

    public function __construct($url)
    {
        $this->url = $url;
    }

    public function pushSection($contents, $context, $engine)
    {
        foreach ($this->ignoreVars as $varName) {
            unset($context[$varName]);
        }

        $contents = trim($contents);
        $key = sha1($contents);

        $this->sections[$key] = ['engine' => $engine, 'view' => $contents];
        $this->contexts[$key] = $context;

        return sprintf('<no_cache_section:%s>', $key);
    }

    public function getContext($region)
    {
        return $this->contexts[$region];
    }

    public function getView($region)
    {
        return $this->sections[$region];
    }

    public function write()
    {
        Cache::forever('nocache::session.'.md5($this->url), [
            'contexts' => $this->contexts,
            'sections' => $this->sections,
        ]);
    }

    public function restore()
    {
        $session = Cache::get('nocache::session.'.md5($this->url));

        $this->contexts = $session['contexts'] ?? [];
        $this->sections = $session['sections'] ?? [];

        return $this;
    }
}

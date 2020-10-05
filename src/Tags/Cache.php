<?php

namespace Statamic\Tags;

use Illuminate\Support\Facades\Cache as LaraCache;
use Statamic\Facades\URL;

class Cache extends Tags
{
    public function index()
    {
        if (! $this->isEnabled()) {
            return [];
        }

        if ($cached = LaraCache::get($key = $this->getCacheKey())) {
            return $cached;
        }

        LaraCache::put($key, $html = (string) $this->parse([]), $this->getCacheLength());

        return $html;
    }

    private function isEnabled()
    {
        if (! config('statamic.system.cache_tags_enabled', true)) {
            return false;
        }

        // Only get requests. This disables the cache during live preview.
        return request()->method() === 'GET';
    }

    private function getCacheKey()
    {
        if ($this->params->has('key')) {
            return $this->params->get('key');
        }

        $hash = [
            'content' => $this->content,
            'params' => $this->params->all(),
        ];

        if ($this->params->get('scope', 'site') === 'page') {
            $hash['url'] = URL::makeAbsolute(URL::getCurrent());
        }

        return 'statamic.cache-tag.'.md5(json_encode($hash));
    }

    private function getCacheLength()
    {
        if (! $length = $this->params->get('for')) {
            return null;
        }

        return now()->add('+'.$length);
    }
}

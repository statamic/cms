<?php

namespace Statamic\Tags;

use Illuminate\Support\Facades\Cache as LaraCache;
use Statamic\Facades\Site;
use Statamic\Facades\URL;

class Cache extends Tags
{
    public function index()
    {
        if (! $this->isEnabled()) {
            return [];
        }

        $store = LaraCache::store();

        if (count($tags = $this->params->explode('tags', []))) {
            $store = $store->tags($tags);
        }

        if ($cached = $store->get($key = $this->getCacheKey())) {
            return $cached;
        }

        $store->put($key, $html = (string) $this->parse([]), $this->getCacheLength());

        return $html;
    }

    private function isEnabled()
    {
        if (! config('statamic.system.cache_tags_enabled', true)) {
            return false;
        }

        // Only GET requests. This disables the cache during live preview.
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

        $scope = $this->params->get('scope', 'site');

        if ($scope === 'site') {
            $hash['site'] = Site::current()->handle();
        }

        if ($scope === 'page') {
            $hash['url'] = URL::makeAbsolute(URL::getCurrent());
        }

        if ($scope === 'user') {
            $hash['user'] = ($user = auth()->user()) ? $user->id : 'guest';
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

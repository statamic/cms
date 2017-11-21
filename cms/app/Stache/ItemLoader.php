<?php

namespace Statamic\Stache;

use Illuminate\Support\Collection;
use Statamic\API\Cache;
use Statamic\API\Str;

class ItemLoader
{
    /**
     * @var Stache
     */
    private $stache;

    /**
     * @var Repository
     */
    private $repo;

    /**
     * @param Stache $stache
     */
    public function __construct(Stache $stache)
    {
        $this->stache = $stache;
    }

    /**
     * Load the items
     *
     * @param Repository $repo
     * @return Collection
     */
    public function load(Repository $repo)
    {
        $this->repo = $repo;

        return collect(Cache::get('stache::keys', []))->filter(function ($key) {
            return $key === $this->repo->cacheKey().'/data';
        })->map(function ($key) {
            return Cache::get("stache::$key");
        })->flatMap(function ($collection) {
            $cache_key = $this->repo->cacheKey();
            $key = (Str::contains($cache_key, '/')) ? explode('/', $cache_key)[0] : $cache_key;
            $repo = $this->stache->driver($key);
            return $repo->load(collect($collection));
        });
    }
}

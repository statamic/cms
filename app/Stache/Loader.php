<?php

namespace Statamic\Stache;

use Illuminate\Support\Facades\Cache;

class Loader
{
    protected $stache;

    public function __construct($stache)
    {
        $this->stache = $stache;
    }

    public function load()
    {
        $meta = $this->getMetaFromCache();

        $meta->each(function ($data, $key) {
            $this->stache->store($key)->loadMeta($data);
        });

        $this->stache->meta($meta);
    }

    public function getMetaFromCache()
    {
        return $this->stache->stores()->mapWithKeys(function ($store) {
            if ($meta = $store->getMetaFromCache()) {
                return $meta;
            }

            throw new EmptyStacheException;
        });
    }
}

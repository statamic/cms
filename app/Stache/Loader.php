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
        if (! $meta = $this->get('meta')) {
            throw new EmptyStacheException;
        }

        collect($meta)->each(function ($data, $key) {
            $this->stache->store($key)
                ->setPaths($data['paths'])
                ->setUris($data['uris']);
        });

        $this->stache->meta($meta);
    }

    protected function get($key)
    {
        return Cache::get("stache::$key");
    }
}

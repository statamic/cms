<?php

namespace Statamic\Stache;

use Statamic\Stache\Stores\Store;

class Stache
{
    const TEMP_COLD = 'cold';
    const TEMP_WARM = 'warm';

    protected $temperature;
    protected $sites;
    protected $meta;
    protected $keys;
    protected $config;
    protected $stores;

    public function __construct()
    {
        $this->temperature = SELF::TEMP_COLD;
        $this->stores = collect();
    }

    public function isCold()
    {
        return $this->temperature === self::TEMP_COLD;
    }

    public function isWarm()
    {
        return $this->temperature === self::TEMP_WARM;
    }

    public function heat()
    {
        $this->temperature = self::TEMP_WARM;
    }

    public function cool()
    {
        $this->temperature = self::TEMP_COLD;
    }

    public function sites($sites = null)
    {
        if (! $sites) {
            return $this->sites;
        }

        $this->sites = collect($sites);

        return $this;
    }

    public function defaultSite()
    {
        return $this->sites->first();
    }

    public function meta($meta = null)
    {
        if (! $meta) {
            return $this->meta;
        }

        $this->meta = collect($meta);

        return $this;
    }

    public function keys($keys = null)
    {
        if ($keys === null) {
            return $this->keys;
        }

        $this->keys = collect($keys);

        return $this;
    }

    public function config($config = null)
    {
        if (! $config) {
            return $this->config;
        }

        $this->config = $config;

        return $this;
    }

    public function registerStore(Store $store)
    {
        $this->stores->put($store->key(), $store);

        return $this;
    }

    public function stores()
    {
        return $this->stores;
    }

    public function store($key)
    {
        return $this->stores->get($key);
    }

    public function load()
    {
        (new Loader($this))->load();

        return $this;
    }

    public function boot()
    {
        (new Bootstrapper)->boot($this);
    }
}

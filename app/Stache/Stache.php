<?php

namespace Statamic\Stache;

use Statamic\Stache\Stores\Store;

class Stache
{
    const TEMP_COLD = 'cold';
    const TEMP_WARM = 'warm';

    protected $bootstrapper;
    protected $booted = false;
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
        $this->boot();

        return $this->stores;
    }

    public function store($key)
    {
        if (str_contains($key, '::')) {
            list($parent, $child) = explode('::', $key);
            return $this->stores()->get($parent)->store($child);
        }

        return $this->stores()->get($key);
    }

    public function load()
    {
        (new Loader($this))->load();

        return $this;
    }

    public function boot()
    {
        if (! $this->booted) {
            $this->booted = true;
            tap($this->bootstrapper ?? new Bootstrapper)->boot($this);
        }

        return $this;
    }

    public function setBootstrapper($bootstrapper)
    {
        $this->bootstrapper = $bootstrapper;

        return $this;
    }

    public function hasBooted()
    {
        return $this->booted;
    }
}

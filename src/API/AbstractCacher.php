<?php

namespace Statamic\API;

use Illuminate\Support\Carbon;

abstract class AbstractCacher implements Cacher
{
    /**
     * @var \Illuminate\Support\Collection
     */
    private $config;

    /**
     * Create cacher instance.
     *
     * @param  array  $config
     */
    public function __construct($config)
    {
        $this->config = collect($config);
    }

    /**
     * Get a config value.
     *
     * @param  mixed  $key
     * @param  mixed  $default
     * @return mixed
     */
    public function config($key = null, $default = null)
    {
        if (is_null($key)) {
            return $this->config;
        }

        return $this->config->get($key, $default);
    }

    /**
     * Prefix a cache key.
     *
     * @param  string  $key
     * @return string
     */
    protected function normalizeKey($key)
    {
        return "api-cache:$key";
    }

    /**
     * Get cache expiry.
     *
     * @return \Carbon\Carbon
     */
    public function cacheExpiry()
    {
        return Carbon::now()->addMinutes($this->config('expiry'));
    }
}

<?php

namespace Statamic\OAuth;

class Manager
{
    protected static $providers = [];

    public function enabled()
    {
        return config('statamic.oauth.enabled')
            && !empty(config('statamic.oauth.providers'));
    }

    public function provider($provider)
    {
        return static::$providers[$provider] = static::$providers[$provider] ?? new Provider($provider);
    }

    public function providers()
    {
        return collect(config('statamic.oauth.providers'))
            ->map(function ($provider) {
                return $this->provider($provider);
            });
    }
}
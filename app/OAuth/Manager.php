<?php

namespace Statamic\OAuth;

class Manager
{
    public function enabled()
    {
        return config('statamic.oauth.enabled')
            && !empty(config('statamic.oauth.providers'));
    }

    public function provider($provider)
    {
        return new Provider($provider);
    }

    public function providers()
    {
        return collect(config('statamic.oauth.providers'))
            ->map(function ($provider) {
                return $this->provider($provider);
            });
    }
}
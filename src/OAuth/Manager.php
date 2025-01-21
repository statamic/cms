<?php

namespace Statamic\OAuth;

class Manager
{
    protected static $providers = [];

    public function enabled()
    {
        return config('statamic.oauth.enabled')
            && ! empty(config('statamic.oauth.providers'));
    }

    public function provider($provider)
    {
        if (isset(static::$providers[$provider])) {
            return static::$providers[$provider];
        }

        return static::$providers[$provider] = $this->providers()->get($provider);
    }

    public function providers()
    {
        return collect(config('statamic.oauth.providers'))
            ->mapWithKeys(function ($value, $key) {
                $provider = $value;
                $config = [];

                // When the $key is NOT an integer, it means the provider has config settings.
                // eg. ['github' => 'GitHub', 'facebook' => ['label' => 'Facebook', 'stateless' => true]]
                if (! is_int($key)) {
                    $provider = $key;
                    $config = is_array($value) ? $value : ['label' => $value];
                }

                return [$provider => new Provider($provider, $config)];
            });
    }
}

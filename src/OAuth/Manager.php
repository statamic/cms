<?php

namespace Statamic\OAuth;

use Statamic\Support\Arr;

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
            ->mapWithKeys(function ($key, $value) {
                $provider = $key;
                $config = null;

                // When the $value is NOT an integer, it means the provider has config settings.
                // eg. ['github' => 'GitHub', 'facebook' => ['label' => 'Facebook', 'stateless' => true]]
                if (! is_int($value)) {
                    $provider = $value;
                    $config = is_array($key)
                      ? $key
                      : ['label' => $key];
                }

                $oAuthProvider = (new Provider($provider))
                    ->label(Arr::get($config, 'label'))
                    ->config($config);

                return [$provider => $oAuthProvider];
            });
    }
}

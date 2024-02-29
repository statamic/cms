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
            ->mapWithKeys(function ($value, $key) {
                $provider = $value;
                $config = null;

                // When the $key is NOT an integer, it means the provider has config settings.
                // eg. ['github' => 'GitHub', 'facebook' => ['label' => 'Facebook', 'stateless' => true]]
                if (! is_int($key)) {
                    $provider = $key;
                    $config = is_array($value)
                      ? $value
                      : ['label' => $value];
                }

                $oAuthProvider = (new Provider($provider))
                    ->label(Arr::get($config, 'label'))
                    ->config($config);

                return [$provider => $oAuthProvider];
            });
    }
}

<?php

namespace Statamic\API;

class OAuth
{
    /**
     * Is OAuth enabled?
     *
     * @return bool
     */
    public static function enabled()
    {
        return Addon::create('OAuthBridge')->isInstalled()
            && class_exists('Statamic\Addons\OAuthBridge\OAuthBridgeServiceProvider');
    }

    /**
     * Get the base oauth route, or a specific one if a provider is passed in
     *
     * @param string|null $provider
     * @return string
     */
    public static function route($provider = null)
    {
        if (! is_null($provider)) {
            return route('oauth', $provider);
        }

        if (! self::enabled()) {
            return 'oauth';
        }

        return app('oauth.bridge')->getRoute();
    }

    /**
     * Get the OAuth providers
     *
     * @return array
     */
    public static function providers()
    {
        return app('oauth.bridge')->getProviders();
    }
}
<?php

namespace Statamic\Auth\Passwords;

use Illuminate\Auth\Passwords\PasswordBrokerManager as BaseManager;
use Illuminate\Support\Str;

class PasswordBrokerManager extends BaseManager
{
    protected function createTokenRepository(array $config)
    {
        $key = $this->app['config']['app.key'];

        if (Str::startsWith($key, 'base64:')) {
            $key = base64_decode(substr($key, 7));
        }

        if (version_compare(app()->version(), '12', '<')) {
            return new TokenRepository(
                $this->app['files'],
                $this->app['hash'],
                $config['table'],
                $key,
                $config['expire'],
                $config['throttle'] ?? 0
            );
        }

        return new LaravelTwelveTokenRepository(
            $this->app['files'],
            $this->app['hash'],
            $config['table'],
            $key,
            ($config['expire'] ?? 60) * 60,
            $config['throttle'] ?? 0
        );
    }
}

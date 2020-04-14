<?php

namespace Statamic\Auth\Passwords;

use Illuminate\Support\Str;
use Illuminate\Auth\Passwords\PasswordBrokerManager as BaseManager;

class PasswordBrokerManager extends BaseManager
{
    protected function createTokenRepository(array $config)
    {
        $key = $this->app['config']['app.key'];

        if (Str::startsWith($key, 'base64:')) {
            $key = base64_decode(substr($key, 7));
        }

        return new TokenRepository(
            $this->app['files'],
            $this->app['hash'],
            $key,
            $config['expire'],
            $config['throttle'] ?? 0
        );
    }
}

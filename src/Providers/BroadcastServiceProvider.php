<?php

namespace Statamic\Providers;

use Illuminate\Support\ServiceProvider;
use Statamic\Statamic;

class BroadcastServiceProvider extends ServiceProvider
{
    public function boot()
    {
        $this->app->booted(function () {
            $variables = $this->enabled() ? $this->variables() : ['enabled' => false];
            Statamic::provideToScript(['broadcasting' => $variables]);
        });
    }

    protected function variables()
    {
        $options = [];

        if (config('broadcasting.default') === 'pusher') {
            $options = [
                'broadcaster' => 'pusher',
                'key' => config('broadcasting.connections.pusher.key'),
                'cluster' => config('broadcasting.connections.pusher.options.cluster'),
                'encrypted' => config('broadcasting.connections.pusher.options.encrypted'),
            ];
        }

        if (config('broadcasting.default') === 'reverb') {
            $options = [
                'broadcaster' => 'reverb',
                'key' => config('broadcasting.connections.reverb.key'),
                'wsHost' => config('broadcasting.connections.reverb.options.host'),
                'wsPort' => config('broadcasting.connections.reverb.options.port', 80),
                'wssPort' => config('broadcasting.connections.reverb.options.port', 443),
                'forceTLS' => config('broadcasting.connections.reverb.options.useTLS'),
                'enabledTransports' => ['ws', 'wss'],
            ];
        }

        return [
            'enabled' => true,
            'endpoint' => $this->authEndpoint(),
            'connection' => config('broadcasting.default'),
            'options' => $options,
        ];
    }

    protected function enabled()
    {
        return in_array(
            \Illuminate\Broadcasting\BroadcastServiceProvider::class,
            array_keys($this->app->getLoadedProviders())
        );
    }

    protected function authEndpoint()
    {
        return config('broadcasting.auth_endpoint', url('/broadcasting/auth'));
    }
}

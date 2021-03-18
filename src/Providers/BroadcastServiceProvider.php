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
        return [
            'enabled' => true,
            'endpoint' => $this->authEndpoint(),
            'pusher' => [
                'key' => config('broadcasting.connections.pusher.key'),
                'cluster' => config('broadcasting.connections.pusher.options.cluster'),
                'encrypted' => config('broadcasting.connections.pusher.options.encrypted'),
            ],
        ];
    }

    protected function enabled()
    {
        return in_array(
            \App\Providers\BroadcastServiceProvider::class,
            array_keys($this->app->getLoadedProviders())
        );
    }

    protected function authEndpoint()
    {
        return config('broadcasting.auth_endpoint', url('/broadcasting/auth'));
    }
}

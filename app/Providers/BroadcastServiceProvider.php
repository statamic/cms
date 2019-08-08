<?php

namespace Statamic\Providers;

use Statamic\Statamic;
use Illuminate\Support\ServiceProvider;
use Illuminate\Broadcasting\BroadcastController;

class BroadcastServiceProvider extends ServiceProvider
{
    public function boot()
    {
        $this->app->booted(function () {
            $this->provideToScript();
        });
    }

    protected function provideToScript()
    {
        return Statamic::provideToScript([
            'broadcasting' => [
                'enabled' => in_array(\App\Providers\BroadcastServiceProvider::class, array_keys($this->app->getLoadedProviders())),
                'endpoint' => $this->authEndpoint(),
                'pusher' => [
                    'key' => config('broadcasting.connections.pusher.key'),
                    'cluster' => config('broadcasting.connections.pusher.options.cluster'),
                    'encrypted' => config('broadcasting.connections.pusher.options.encrypted'),
                ]
            ]
        ]);
    }

    protected function authEndpoint()
    {
        return $this->app['url']->action([BroadcastController::class, 'authenticate']);
    }
}

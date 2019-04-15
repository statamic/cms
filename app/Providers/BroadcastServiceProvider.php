<?php

namespace Statamic\Providers;

use Statamic\Statamic;
use Illuminate\Support\ServiceProvider;

class BroadcastServiceProvider extends ServiceProvider
{
    public function boot()
    {
        Statamic::provideToScript([
            'broadcasting' => [
                'enabled' => in_array(\App\Providers\BroadcastServiceProvider::class, array_keys($this->app->getLoadedProviders())),
                'pusher' => [
                    'key' => config('broadcasting.connections.pusher.key'),
                    'cluster' => config('broadcasting.connections.pusher.options.cluster'),
                    'encrypted' => config('broadcasting.connections.pusher.options.encrypted'),
                ]
            ]
        ]);
    }
}

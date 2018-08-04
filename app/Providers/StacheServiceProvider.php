<?php

namespace Statamic\Providers;

use Statamic\Stache\Stache;
use Statamic\Stache\Stores;
use Illuminate\Support\ServiceProvider;

class StacheServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->singleton(Stache::class, function () {
            return new Stache;
        });

        $this->app->alias(Stache::class, 'stache');
    }

    public function boot()
    {
        $stache = $this->app->make(Stache::class);

        $stache->sites(['en']); // @todo

        $stache->registerStore(
            (new Stores\EntriesStore($stache))->directory(base_path('content/collections/'))
        );

        $stache->boot();
    }
}

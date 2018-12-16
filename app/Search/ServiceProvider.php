<?php

namespace Statamic\Search;

use Statamic\API\Search;
use Statamic\Events\Data\EntrySaved;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider as LaravelServiceProvider;

class ServiceProvider extends LaravelServiceProvider
{
    public function register()
    {
        $this->app->bind(IndexManager::class, function ($app) {
            return new IndexManager($app);
        });
    }

    public function boot()
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                Commands\Insert::class,
                Commands\Update::class
            ]);
        }

        Event::listen(EntrySaved::class, function ($event) {
            $item = $event->data;

            Search::indexes()
                ->filter->shouldIndex($item)
                ->each(function ($index) use ($item) {
                    $index->exists() ? $index->insert($item) : $index->update();
                });
        });
    }
}

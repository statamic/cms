<?php

namespace Statamic\Providers;

use Illuminate\Support\ServiceProvider;
use Statamic\Structures\UriCache;

class DataServiceProvider extends ServiceProvider
{
    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->app->bind(
            \Statamic\Contracts\Structures\Structure::class,
            \Statamic\Structures\Structure::class
        );

        $this->app->bind(
            \Statamic\Contracts\Entries\Collection::class,
            \Statamic\Entries\Collection::class
        );

        $this->app->bind(
            \Statamic\Contracts\Taxonomies\Taxonomy::class,
            \Statamic\Taxonomies\Taxonomy::class
        );

        $this->app->bind(
            \Statamic\Contracts\Assets\AssetContainer::class,
            \Statamic\Assets\AssetContainer::class
        );

        $this->app->bind(
            \Statamic\Contracts\Assets\Asset::class,
            \Statamic\Assets\Asset::class
        );

        $this->app->bind(
            \Statamic\Contracts\Assets\QueryBuilder::class,
            \Statamic\Assets\QueryBuilder::class
        );

        $this->app->singleton(
            \Statamic\CP\Navigation\Nav::class,
            \Statamic\CP\Navigation\Nav::class
        );

        $this->app->bind(
            \Statamic\Contracts\Forms\Form::class,
            \Statamic\Forms\Form::class
        );

        $this->app->bind(
            \Statamic\Contracts\Forms\Formset::class,
            \Statamic\Forms\Formset::class
        );

        $this->app->bind(
            \Statamic\Contracts\Forms\Submission::class,
            \Statamic\Forms\Submission::class
        );

        $this->app->singleton(UriCache::class, function () {
            return new UriCache;
        });
    }
}

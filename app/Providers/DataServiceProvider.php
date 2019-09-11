<?php

namespace Statamic\Providers;

use Statamic\API\Config;
use Illuminate\Support\ServiceProvider;

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
            \Statamic\Contracts\Data\Structures\Structure::class,
            \Statamic\Data\Structures\Structure::class
        );

        $this->app->bind(
            \Statamic\Contracts\Data\Content\PathBuilder::class,
            \Statamic\Data\Content\PathBuilder::class
        );

        $this->app->bind(
            \Statamic\Contracts\Data\Content\UrlBuilder::class,
            \Statamic\Data\Content\UrlBuilder::class
        );

        $this->app->bind(
            \Statamic\Contracts\Data\Entries\Collection::class,
            \Statamic\Data\Entries\Collection::class
        );

        $this->app->bind(
            \Statamic\Contracts\Data\Taxonomies\Taxonomy::class,
            \Statamic\Data\Taxonomies\Taxonomy::class
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
    }
}

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
            \Statamic\Contracts\Data\Content\OrderParser::class,
            \Statamic\Data\Content\OrderParser::class
        );

        $this->app->bind(
            \Statamic\Contracts\Data\Content\StatusParser::class,
            \Statamic\Data\Content\StatusParser::class
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
            \Statamic\Contracts\Data\Entries\EntryFactory::class,
            \Statamic\Data\Entries\EntryFactory::class
        );

        $this->app->bind(
            \Statamic\Contracts\Data\Entries\Collection::class,
            \Statamic\Data\Entries\Collection::class
        );

        $this->app->bind(
            \Statamic\Contracts\Data\Taxonomies\TermFactory::class,
            \Statamic\Data\Taxonomies\TermFactory::class
        );

        $this->app->bind(
            \Statamic\Contracts\Data\Taxonomies\Taxonomy::class,
            \Statamic\Data\Taxonomies\Taxonomy::class
        );

        $this->app->bind(
            \Statamic\Contracts\Data\Globals\GlobalFactory::class,
            \Statamic\Data\Globals\GlobalFactory::class
        );

        $this->app->bind(
            \Statamic\Contracts\Assets\AssetContainer::class,
            \Statamic\Assets\AssetContainer::class
        );

        $this->app->bind(
            \Statamic\Contracts\Assets\AssetFactory::class,
            \Statamic\Assets\AssetFactory::class
        );

        $this->app->bind(
            \Statamic\Contracts\Assets\AssetContainerFactory::class,
            \Statamic\Assets\AssetContainerFactory::class
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

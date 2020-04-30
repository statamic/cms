<?php

namespace Statamic\Providers;

use Facades\Statamic\Imaging\GlideServer;
use Illuminate\Support\ServiceProvider;
use League\Glide\Server;
use Statamic\Contracts\Imaging\ImageManipulator;
use Statamic\Contracts\Imaging\UrlBuilder;
use Statamic\Facades\Config;
use Statamic\Facades\URL;
use Statamic\Imaging\GlideImageManipulator;
use Statamic\Imaging\GlideUrlBuilder;
use Statamic\Imaging\ImageGenerator;
use Statamic\Imaging\PresetGenerator;
use Statamic\Imaging\StaticUrlBuilder;

class GlideServiceProvider extends ServiceProvider
{
    public $defer = true;

    public function register()
    {
        $this->app->bind(UrlBuilder::class, function () {
            return $this->getBuilder();
        });

        $this->app->bind(ImageManipulator::class, function () {
            return new GlideImageManipulator(
                $this->app->make(UrlBuilder::class)
            );
        });

        $this->app->singleton(Server::class, function () {
            return GlideServer::create();
        });

        $this->app->bind(PresetGenerator::class, function ($app) {
            return new PresetGenerator(
                $app->make(ImageGenerator::class),
                Config::getImageManipulationPresets()
            );
        });
    }

    private function getBuilder()
    {
        $route = Config::get('statamic.assets.image_manipulation.route');

        if (Config::get('statamic.assets.image_manipulation.cache')) {
            return new StaticUrlBuilder($this->app->make(ImageGenerator::class), [
                'route' => URL::prependSiteUrl($route),
            ]);
        }

        return new GlideUrlBuilder([
            'key' => (Config::get('statamic.assets.image_manipulation.secure')) ? Config::getAppKey() : null,
            'route' => $route,
        ]);
    }

    public function provides()
    {
        return [ImageManipulator::class, Server::class, PresetGenerator::class];
    }
}

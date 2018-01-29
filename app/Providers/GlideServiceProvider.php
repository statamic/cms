<?php

namespace Statamic\Providers;

use Statamic\API\URL;
use Statamic\API\Image;
use League\Glide\Server;
use Statamic\API\Config;
use League\Glide\ServerFactory;
use Statamic\Imaging\ImageGenerator;
use Statamic\Imaging\GlideUrlBuilder;
use Statamic\Imaging\PresetGenerator;
use Statamic\Imaging\StaticUrlBuilder;
use Illuminate\Support\ServiceProvider;
use Statamic\Contracts\Imaging\UrlBuilder;
use Statamic\Imaging\GlideImageManipulator;
use Statamic\Contracts\Imaging\ImageManipulator;
// use League\Glide\Responses\LaravelResponseFactory;
use Statamic\Imaging\ResponseFactory as LaravelResponseFactory;

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
            $presets = Config::getImageManipulationPresets();

            if (config('statamic.cp.enabled')) {
                $presets = array_merge($presets, Image::getCpImageManipulationPresets());
            }

            return ServerFactory::create([
                'source'   => base_path(), // this gets overriden on the fly by the image generator
                'cache'    => storage_path('glide'),
                'base_url' => Config::get('statamic.assets.image_manipulation.route', 'img'),
                'response' => new LaravelResponseFactory(app('request')),
                'driver'   => Config::get('statamic.assets.image_manipulation.driver'),
                'cache_with_file_extensions' => true,
                'presets' => $presets,
            ]);
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

        if (Config::get('statamic.assets.image_manipulation.cached')) {
            return new StaticUrlBuilder($this->app->make(ImageGenerator::class), [
                'route' => URL::prependSiteUrl($route)
            ]);
        }

        return new GlideUrlBuilder([
            'key' => (Config::get('statamic.assets.image_manipulation.secure')) ? Config::getAppKey() : null,
            'route' => $route
        ]);
    }

    public function provides()
    {
        return [ImageManipulator::class, Server::class, PresetGenerator::class];
    }
}

<?php

namespace Statamic\Providers;

use Statamic\DataStore;
use Statamic\Extensions\FileStore;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\ServiceProvider;

class ExtensionServiceProvider extends ServiceProvider
{
    /**
     * Tags bundled with Statamic.
     *
     * @var array
     */
    protected $bundledTags = [
        'asset', 'assets', 'cache', 'can', 'collection', 'dump', 'entries', 'env', 'form',
        'get_content', 'get_files', 'get_value', 'glide', 'in', 'is', 'link', 'locales',
        'markdown', 'member', 'nav', 'not_found', 'oauth', 'obfuscate', 'pages', 'parent',
        'partial', 'path', 'protect', 'redirect', 'relate', 'rotate', 'routes', 'search',
        'section', 'taxonomy', 'theme', 'trans', 'trans_choice', 'user', 'widont', 'yields',
    ];

    /**
     * Aliases for tags bundled with Statamic.
     *
     * @var array
     */
    protected $bundledTagAliases = [
        'switch' => 'Rotate',
        '404' => 'NotFound',
        'yield' => 'Yields',
    ];

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->registerTags();
    }

    /**
     * Register tags.
     *
     * @return void
     */
    protected function registerTags()
    {
        $this->app->instance('statamic.tags', collect());

        $this->registerBundledTags();
        $this->registerAppTags();
    }

    /**
     * Register bundled tags.
     *
     * @return void
     */
    protected function registerBundledTags()
    {
        foreach ($this->bundledTags as $tag) {
            $studly = studly_case($tag);
            $this->app['statamic.tags'][$tag] = "Statamic\\Addons\\{$studly}\\{$studly}Tags";
        }

        foreach ($this->bundledTagAliases as $alias => $actual) {
            $this->app['statamic.tags'][$alias] = "Statamic\\Addons\\{$actual}\\{$actual}Tags";
        }
    }

    /**
     * Register tags located in the App directory.
     *
     * This prevents requiring users to manually bind their tags.
     *
     * @return void
     */
    protected function registerAppTags()
    {
        foreach ($this->app['files']->files(app_path('Tags')) as $file) {
            $tag = snake_case($class = $file->getBasename('.php'));
            $this->app['statamic.tags'][$tag] = "App\\Tags\\{$class}";
        }
    }
}

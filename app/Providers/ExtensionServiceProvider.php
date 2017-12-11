<?php

namespace Statamic\Providers;

use Statamic\DataStore;
use Statamic\Extend\Modifier;
use Statamic\View\BaseModifiers;
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
     * Fieldtypes bundled with Statamic.
     *
     * @var array
     */
    protected $bundledFieldtypes = [
        'arr', 'asset_container', 'asset_folder', 'assets', 'checkboxes', 'collection', 'collections',
        'date', 'fields', 'fieldset', 'form', 'grid', 'hidden', 'integer', 'lists', 'locale_settings', 'markdown',
        'pages', 'partial', 'radio', 'redactor', 'redactor_settings', 'relate', 'replicator', 'replicator_sets',
        'revealer', 'section', 'select', 'suggest', 'table', 'tags', 'taxonomy', 'template', 'text', 'textarea',
        'theme', 'time', 'title', 'toggle', 'user', 'user_groups', 'user_password', 'user_roles', 'yaml',
    ];

    /**
     * Aliases for fieldtypes bundled with Statamic.
     *
     * @var array
     */
    protected $bundledFieldtypeAliases = [
        'array' => 'Arr',
        'list' => 'lists'
    ];

    /**
     * Aliases for modifiers bundled with Statamic.
     *
     * @var array
     */
    protected $bundledModifierAliases = [
        '+' => 'add',
        '-' => 'subtract',
        '*' => 'multiply',
        '/' => 'divide',
        '%' => 'mod',
        '^' => 'exponent',
        'dd' => 'dump',
        'ago' => 'relative',
        'until' => 'relative',
        'since' => 'relative',
        'specialchars' => 'sanitize',
        'htmlspecialchars' => 'sanitize',
        'striptags' => 'stripTags',
        'join' => 'joinplode',
        'implode' => 'joinplode',
        'list' => 'joinplode',
        'piped' => 'optionList',
        'json' => 'toJson',
        'email' => 'obfuscateEmail',
        'l10n' => 'formatLocalized',
        'lowercase' => 'lower',
        '85' => 'slackEasterEgg',
        'tz' => 'timezone',
        'in_future' => 'isFuture',
        'inPast' => 'isPast',
        'in_past' => 'isPast',
        'as' => 'scopeAs',
    ];

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->registerTags();
        $this->registerModifiers();
        $this->registerFieldtypes();
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

    /**
     * Register tags.
     *
     * @return void
     */
    protected function registerModifiers()
    {
        $this->app->instance('statamic.modifiers', collect());

        $this->registerBundledModifiers();
        $this->registerAppModifiers();
    }

    /**
     * Register bundled tags.
     *
     * @return void
     */
    protected function registerBundledModifiers()
    {
        $methods = array_diff(
            get_class_methods(BaseModifiers::class),
            get_class_methods(Modifier::class)
        );

        foreach ($methods as $method) {
            $this->app['statamic.modifiers'][$method] = "Statamic\\View\\BaseModifiers@{$method}";
        }

        foreach ($this->bundledModifierAliases as $alias => $actual) {
            $this->app['statamic.modifiers'][$alias] = "Statamic\\View\\BaseModifiers@{$actual}";
        }
    }

    /**
     * Register modifiers located in the App directory.
     *
     * This prevents requiring users to manually bind their modifiers.
     *
     * @return void
     */
    protected function registerAppModifiers()
    {
        foreach ($this->app['files']->files(app_path('Modifiers')) as $file) {
            $modifier = snake_case($class = $file->getBasename('.php'));
            $this->app['statamic.modifiers'][$modifier] = "App\\Modifiers\\{$class}";
        }
    }

    /**
     * Register fieldtypes.
     *
     * @return void
     */
    protected function registerFieldtypes()
    {
        $this->app->instance('statamic.fieldtypes', collect());

        $this->registerBundledFieldtypes();
    }

    /**
     * Register bundled tags.
     *
     * @return void
     */
    protected function registerBundledFieldtypes()
    {
        foreach ($this->bundledFieldtypes as $tag) {
            $studly = studly_case($tag);
            $this->app['statamic.fieldtypes'][$tag] = "Statamic\\Addons\\{$studly}\\{$studly}Fieldtype";
        }

        foreach ($this->bundledFieldtypeAliases as $alias => $actual) {
            $this->app['statamic.fieldtypes'][$alias] = "Statamic\\Addons\\{$actual}\\{$actual}Fieldtype";
        }
    }
}

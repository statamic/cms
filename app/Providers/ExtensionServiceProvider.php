<?php

namespace Statamic\Providers;

use Statamic\DataStore;
use Statamic\Extend\Modifier;
use Statamic\View\BaseModifiers;
use Statamic\Extensions\FileStore;
use Illuminate\Support\Facades\Cache;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\ServiceProvider;
use Statamic\Extend\Management\Manifest;
use Illuminate\Console\DetectsApplicationNamespace;

class ExtensionServiceProvider extends ServiceProvider
{
    use DetectsApplicationNamespace;

    /**
     * Tags bundled with Statamic.
     *
     * @var array
     */
    protected $bundledTags = [
        'asset', 'assets', 'cache', 'can', 'collection', 'dump', 'entries', 'env',
        'get_content', 'get_files', 'get_value', 'glide', 'in', 'is', 'link', 'locales',
        'markdown', 'member', 'mix', 'nav', 'not_found', 'oauth', 'obfuscate', 'pages', 'parent',
        'partial', 'path', 'redirect', 'relate', 'rotate', 'routes',
        'section', 'taxonomy', 'theme', 'trans', 'trans_choice', 'users', 'widont', 'yields',
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
        'arr', 'asset_container', 'asset_folder', 'bard', 'checkboxes', 'code', 'collection', 'collections',
        'date', 'fieldset', 'hidden', 'integer', 'lists', 'locale_settings', 'markdown',
        'pages', 'partial', 'radio', 'redactor', 'redactor_settings', 'relate', 'replicator', 'replicator_sets',
        'theme', 'time', 'title', 'toggle', 'user_groups', 'user_roles', 'video', 'yaml',
        'revealer', 'section', 'select', 'slug', 'suggest', 'table', 'tags', 'taxonomy', 'template', 'text', 'textarea',
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
     * Widgets bundled with Statamic.
     *
     * @var array
     */
    protected $bundledWidgets = [
        'collection', 'template', 'updater',
    ];

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->instance(Manifest::class, new Manifest(
            new Filesystem,
            $this->app->basePath(),
            $this->app->bootstrapPath().'/cache/addons.php'
        ));

        $this->registerTags();
        $this->registerModifiers();
        $this->registerFieldtypes();
        $this->registerFilters();
        $this->registerWidgets();
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
        $this->registerExtensionsInAppFolder('Tags');
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

        $this->app['statamic.tags']['form'] = \Statamic\Forms\Tags::class;
        $this->app['statamic.tags']['user'] = \Statamic\Auth\UserTags::class;
        $this->app['statamic.tags']['protect'] = \Statamic\Auth\Protect\Tags::class;
        $this->app['statamic.tags']['search'] = \Statamic\Search\Tags::class;
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
        $this->registerExtensionsInAppFolder('Modifiers');
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
     * Register fieldtypes.
     *
     * @return void
     */
    protected function registerFieldtypes()
    {
        $this->app->instance('statamic.fieldtypes', collect());

        $this->registerBundledFieldtypes();
        $this->registerExtensionsInAppFolder('Fieldtypes');
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

        $this->app['statamic.fieldtypes']['assets'] = \Statamic\Fields\Fieldtypes\Assets::class;
        $this->app['statamic.fieldtypes']['form'] = \Statamic\Forms\Fieldtype::class;
        $this->app['statamic.fieldtypes']['grid'] = \Statamic\Fields\Fieldtypes\Grid::class;
        $this->app['statamic.fieldtypes']['fields'] = \Statamic\Fields\Fieldtypes\NestedFields::class;
        $this->app['statamic.fieldtypes']['relationship'] = \Statamic\Fields\Fieldtypes\Relationship::class;
    }

    /**
     * Register filters.
     *
     * @return void
     */
    protected function registerFilters()
    {
        $this->app->instance('statamic.filters', collect());

        $this->registerBundledFilters();
        $this->registerExtensionsInAppFolder('Filters');
    }

    /**
     * Register bundled filters.
     *
     * @return void
     */
    protected function registerBundledFilters()
    {
        // foreach ($this->bundledFieldtypes as $tag) {
        //     $studly = studly_case($tag);
        //     $this->app['statamic.fieldtypes'][$tag] = "Statamic\\Addons\\{$studly}\\{$studly}Fieldtype";
        // }

        // foreach ($this->bundledFieldtypeAliases as $alias => $actual) {
        //     $this->app['statamic.fieldtypes'][$alias] = "Statamic\\Addons\\{$actual}\\{$actual}Fieldtype";
        // }

        // $this->app['statamic.fieldtypes']['assets'] = \Statamic\Fields\Fieldtypes\Assets::class;
        // $this->app['statamic.fieldtypes']['form'] = \Statamic\Forms\Fieldtype::class;
        // $this->app['statamic.fieldtypes']['grid'] = \Statamic\Fields\Fieldtypes\Grid::class;
        // $this->app['statamic.fieldtypes']['fields'] = \Statamic\Fields\Fieldtypes\NestedFields::class;
        // $this->app['statamic.fieldtypes']['relationship'] = \Statamic\Fields\Fieldtypes\Relationship::class;
    }

    /**
     * Register widgets.
     *
     * @return void
     */
    protected function registerWidgets()
    {
        $this->app->instance('statamic.widgets', collect());

        $this->registerBundledWidgets();
        $this->registerExtensionsInAppFolder('Widgets');
    }

    /**
     * Register bundled widgets.
     *
     * @return void
     */
    protected function registerBundledWidgets()
    {
        foreach ($this->bundledWidgets as $widget) {
            $studly = studly_case($widget);
            $this->app['statamic.widgets'][$widget] = "Statamic\\Addons\\{$studly}\\{$studly}Widget";
        }

        $this->app['statamic.widgets']['form'] = \Statamic\Forms\Widget::class;
    }

    /**
     * Register extensions in a specific app folder.
     *
     * This prevents requiring users to manually bind their extensions.
     *
     * @param string $folder
     * @return void
     */
    protected function registerExtensionsInAppFolder($folder)
    {
        if (! $this->app['files']->exists($path = app_path($folder))) {
            return;
        }

        foreach ($this->app['files']->files($path) as $file) {
            $id = camel_case($class = $file->getBasename('.php'));
            $extensionType = strtolower($folder);
            $this->app["statamic.{$extensionType}"][$id] = $this->getAppNamespace() . "{$folder}\\{$class}";
        }
    }
}

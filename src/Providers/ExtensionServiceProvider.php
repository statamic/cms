<?php

namespace Statamic\Providers;

use Statamic\Tags;
use Statamic\Actions;
use Statamic\Fieldtypes;
use Statamic\Query\Scopes;
use Statamic\Modifiers\Modifier;
use Statamic\Extensions\FileStore;
use Statamic\Modifiers\CoreModifiers;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\ServiceProvider;
use Statamic\Extend\Manifest;
use Illuminate\Console\DetectsApplicationNamespace;

class ExtensionServiceProvider extends ServiceProvider
{
    use DetectsApplicationNamespace;

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
        'tz' => 'timezone',
        'inFuture' => 'isFuture',
        'inPast' => 'isPast',
        'as' => 'alias',
    ];

    /**
     * Widgets bundled with Statamic.
     *
     * @var array
     */
    protected $bundledWidgets = [
        'getting-started', 'collection', 'template', 'updater', 'form'
    ];

    protected $fieldtypes = [
        Fieldtypes\Arr::class,
        Fieldtypes\AssetContainer::class,
        Fieldtypes\AssetFolder::class,
        Fieldtypes\Assets\Assets::class,
        Fieldtypes\Bard::class,
        Fieldtypes\Bard\Buttons::class,
        Fieldtypes\Blueprints::class,
        Fieldtypes\Checkboxes::class,
        Fieldtypes\Code::class,
        Fieldtypes\CollectionRoutes::class,
        Fieldtypes\Collections::class,
        Fieldtypes\Color::class,
        Fieldtypes\Date::class,
        Fieldtypes\Entries::class,
        Fieldtypes\Grid::class,
        Fieldtypes\Hidden::class,
        Fieldtypes\Integer::class,
        Fieldtypes\Link::class,
        Fieldtypes\Lists::class,
        Fieldtypes\Markdown::class,
        Fieldtypes\NestedFields::class,
        Fieldtypes\Radio::class,
        Fieldtypes\Range::class,
        Fieldtypes\Replicator::class,
        Fieldtypes\Revealer::class,
        Fieldtypes\Section::class,
        Fieldtypes\Select::class,
        Fieldtypes\Sets::class,
        Fieldtypes\Sites::class,
        Fieldtypes\Structures::class,
        Fieldtypes\Slug::class,
        Fieldtypes\Table::class,
        Fieldtypes\Tags::class,
        Fieldtypes\Taxonomy::class,
        Fieldtypes\Taxonomies::class,
        Fieldtypes\Template::class,
        Fieldtypes\Text::class,
        Fieldtypes\Textarea::class,
        Fieldtypes\Time::class,
        Fieldtypes\Toggle::class,
        Fieldtypes\UserGroups::class,
        Fieldtypes\UserRoles::class,
        Fieldtypes\Users::class,
        Fieldtypes\Video::class,
        Fieldtypes\Yaml::class,
        \Statamic\Forms\Fieldtype::class,
    ];

    protected $tags = [
        Tags\Asset::class,
        Tags\Assets::class,
        Tags\Cache::class,
        Tags\Can::class,
        Tags\Collection\Collection::class,
        Tags\Dd::class,
        Tags\Dump::class,
        Tags\GetContent::class,
        Tags\GetFiles::class,
        Tags\Glide::class,
        Tags\In::class,
        Tags\Is::class,
        Tags\Iterate::class,
        Tags\Link::class,
        Tags\Locales::class,
        Tags\Markdown::class,
        Tags\Member::class,
        Tags\Mix::class,
        Tags\Nav::class,
        Tags\NotFound::class,
        Tags\Obfuscate::class,
        Tags\ParentTags::class,
        Tags\Partial::class,
        Tags\Path::class,
        Tags\Query::class,
        Tags\Redirect::class,
        Tags\Relate::class,
        Tags\Rotate::class,
        Tags\Route::class,
        Tags\Scope::class,
        Tags\Set::class,
        Tags\Section::class,
        Tags\Session::class,
        Tags\Structure::class,
        Tags\Svg::class,
        Tags\Taxonomy\Taxonomy::class,
        Tags\Theme::class,
        Tags\Trans::class,
        Tags\TransChoice::class,
        Tags\Users::class,
        Tags\Widont::class,
        Tags\Yields::class,
        \Statamic\Forms\Tags::class,
        \Statamic\Auth\UserTags::class,
        \Statamic\Auth\Protect\Tags::class,
        \Statamic\OAuth\Tags::class,
        \Statamic\Search\Tags::class,
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
        $this->registerScopes();
        $this->registerActions();
        $this->registerWidgets();
    }

    /**
     * Register tags.
     *
     * @return void
     */
    protected function registerTags()
    {
        $parent = 'statamic.tags';

        $this->registerParent($parent);

        foreach ($this->tags as $tag) {
            $this->registerExtension($tag, $parent);
            $this->registerAliases($tag, $parent);
        }

        $this->registerExtensionsInAppFolder('Tags', \Statamic\Tags\Tags::class);
    }

    /**
     * Register modifiers.
     *
     * @return void
     */
    protected function registerModifiers()
    {
        $parent = 'statamic.modifiers';

        $this->registerParent($parent);
        $this->registerBundledModifiers($parent);
        $this->registerExtensionsInAppFolder('Modifiers', \Statamic\Modifiers\Modifier::class);
    }

    /**
     * Register bundled modifiers.
     *
     * @param string $parent
     * @return void
     */
    protected function registerBundledModifiers($parent)
    {
        $methods = array_diff(
            get_class_methods(CoreModifiers::class),
            get_class_methods(Modifier::class)
        );

        foreach ($methods as $method) {
            $this->app[$parent][$method] = "Statamic\\Modifiers\\CoreModifiers@{$method}";
        }

        foreach ($this->bundledModifierAliases as $alias => $actual) {
            $this->app[$parent][$alias] = "Statamic\\Modifiers\\CoreModifiers@{$actual}";
        }
    }

    /**
     * Register fieldtypes.
     *
     * @return void
     */
    protected function registerFieldtypes()
    {
        $parent = 'statamic.fieldtypes';

        $this->registerParent($parent);

        foreach ($this->fieldtypes as $fieldtype) {
            $this->registerExtension($fieldtype, $parent);
        }

        $this->registerExtensionsInAppFolder('Fieldtypes', \Statamic\Fields\Fieldtype::class);
    }

    /**
     * Register scopes.
     *
     * @return void
     */
    protected function registerScopes()
    {
        $parent = 'statamic.scopes';

        $scopes = [
            Scopes\Filters\Fields::class,
            Scopes\Filters\Blueprint::class,
            Scopes\Filters\Status::class,
            Scopes\Filters\Site::class,
            Scopes\Filters\UserRole::class,
            Scopes\Filters\UserGroup::class,
            Scopes\Filters\Collection::class,
        ];

        $this->registerParent($parent);

        foreach ($scopes as $scope) {
            $this->registerExtension($scope, $parent);
        }

        $this->registerExtensionsInAppFolder('Scopes', \Statamic\Query\Scopes\Scope::class);
    }

    /**
     * Register actions.
     *
     * @return void
     */
    protected function registerActions()
    {
        $parent = 'statamic.actions';

        $actions = [
            Actions\Delete::class,
            Actions\Publish::class,
            Actions\Unpublish::class,
            Actions\SendPasswordReset::class,
            Actions\MoveAsset::class,
            Actions\RenameAsset::class,
        ];

        $this->registerParent($parent);

        foreach ($actions as $action) {
            $this->registerExtension($action, $parent);
        }

        $this->registerExtensionsInAppFolder('Actions', \Statamic\Actions\Action::class);
    }

    /**
     * Register widgets.
     *
     * @return void
     */
    protected function registerWidgets()
    {
        $parent = 'statamic.widgets';

        $widgets = [
            \Statamic\Widgets\Collection::class,
            \Statamic\Widgets\GettingStarted::class,
            \Statamic\Widgets\Header::class,
            \Statamic\Widgets\Template::class,
            \Statamic\Widgets\Updater::class,
            \Statamic\Forms\Widget::class,
        ];

        $this->registerParent($parent);

        foreach ($widgets as $widget) {
            $this->registerExtension($widget, $parent);
        }

        $this->registerExtensionsInAppFolder('Widgets', \Statamic\Widgets\Widget::class);
    }

    /**
     * Register parent.
     *
     * @param string $parent
     * @return void
     */
    protected function registerParent($parent)
    {
        $this->app->instance($parent, collect());
    }

    /**
     * Register extension.
     *
     * @param string $extension
     * @param string $parent
     * @return void
     */
    protected function registerExtension($extension, $parent)
    {
        $this->app[$parent][$extension::handle()] = $extension;
    }

    /**
     * Register aliases.
     *
     * @param string $extension
     * @param string $parent
     * @return void
     */
    protected function registerAliases($extension, $parent)
    {
        foreach ($extension::aliases() as $alias) {
            $this->app[$parent][$alias] = $extension;
        }
    }

    /**
     * Register extensions in a specific app folder.
     *
     * This prevents requiring users to manually bind their extensions.
     *
     * @param string $folder
     * @param string $requiredClass
     * @return void
     */
    protected function registerExtensionsInAppFolder($folder, $requiredClass)
    {
        if (! $this->app['files']->exists($path = app_path($folder))) {
            return;
        }

        foreach ($this->app['files']->files($path) as $file) {
            $class = $file->getBasename('.php');
            $fqcn = $this->getAppNamespace() . "{$folder}\\{$class}";
            if (is_subclass_of($fqcn, $requiredClass)) {
                $fqcn::register();
            }
        }
    }
}

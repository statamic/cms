<?php

namespace Statamic\Providers;

use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Env;
use Illuminate\Support\ServiceProvider;
use Statamic\Actions;
use Statamic\Actions\Action;
use Statamic\Addons\Manifest;
use Statamic\Dictionaries;
use Statamic\Dictionaries\Dictionary;
use Statamic\Fields\Fieldtype;
use Statamic\Fieldtypes;
use Statamic\Forms\JsDrivers;
use Statamic\Modifiers\CoreModifiers;
use Statamic\Modifiers\Modifier;
use Statamic\Query\Scopes;
use Statamic\Query\Scopes\Scope;
use Statamic\Statamic;
use Statamic\Support\Str;
use Statamic\Tags;
use Statamic\UpdateScripts as Updates;
use Statamic\Widgets;
use Statamic\Widgets\Widget;

class ExtensionServiceProvider extends ServiceProvider
{
    protected $actions = [
        'copy_asset_url' => Actions\CopyAssetUrl::class,
        'copy_password_reset_link' => Actions\CopyPasswordResetLink::class,
        'delete' => Actions\Delete::class,
        'delete_multisite_entry' => Actions\DeleteMultisiteEntry::class,
        'disable_two_factor_authentication' => Actions\DisableTwoFactorAuthentication::class,
        'download_asset' => Actions\DownloadAsset::class,
        'download_asset_folder' => Actions\DownloadAssetFolder::class,
        'duplicate_asset' => Actions\DuplicateAsset::class,
        'duplicate_entry' => Actions\DuplicateEntry::class,
        'duplicate_form' => Actions\DuplicateForm::class,
        'duplicate_term' => Actions\DuplicateTerm::class,
        'publish' => Actions\Publish::class,
        'unpublish' => Actions\Unpublish::class,
        'send_password_reset' => Actions\SendPasswordReset::class,
        'assign_roles' => Actions\AssignRoles::class,
        'assign_groups' => Actions\AssignGroups::class,
        'move_asset' => Actions\MoveAsset::class,
        'rename_asset' => Actions\RenameAsset::class,
        'replace_asset' => Actions\ReplaceAsset::class,
        'reupload_asset' => Actions\ReuploadAsset::class,
        'move_asset_folder' => Actions\MoveAssetFolder::class,
        'rename_asset_folder' => Actions\RenameAssetFolder::class,
        'impersonate' => Actions\Impersonate::class,
    ];

    protected $dictionaries = [
        'countries' => Dictionaries\Countries::class,
        'currencies' => Dictionaries\Currencies::class,
        'file' => Dictionaries\File::class,
        'languages' => Dictionaries\Languages::class,
        'locales' => Dictionaries\Locales::class,
        'timezones' => Dictionaries\Timezones::class,
    ];

    protected $fieldtypes = [
        'array' => Fieldtypes\Arr::class,
        'asset_container' => Fieldtypes\AssetContainer::class,
        'asset_folder' => Fieldtypes\AssetFolder::class,
        'assets' => Fieldtypes\Assets\Assets::class,
        'bard' => Fieldtypes\Bard::class,
        'bard_buttons_setting' => Fieldtypes\Bard\Buttons::class,
        'blueprints' => Fieldtypes\Blueprints::class,
        'button_group' => Fieldtypes\ButtonGroup::class,
        'checkboxes' => Fieldtypes\Checkboxes::class,
        'code' => Fieldtypes\Code::class,
        'collection_routes' => Fieldtypes\CollectionRoutes::class,
        'collection_title_formats' => Fieldtypes\CollectionTitleFormats::class,
        'collections' => Fieldtypes\Collections::class,
        'color' => Fieldtypes\Color::class,
        'control_appearance' => Fieldtypes\ControlAppearance::class,
        'date' => Fieldtypes\Date::class,
        'dictionary' => Fieldtypes\Dictionary::class,
        'dictionary_fields' => Fieldtypes\DictionaryFields::class,
        'entries' => Fieldtypes\Entries::class,
        'field_display' => Fieldtypes\FieldDisplay::class,
        'files' => Fieldtypes\Files::class,
        'float' => Fieldtypes\Floatval::class,
        'formatting_locales' => Fieldtypes\FormattingLocales::class,
        'global_set_sites' => Fieldtypes\GlobalSetSites::class,
        'grid' => Fieldtypes\Grid::class,
        'group' => Fieldtypes\Group::class,
        'hidden' => Fieldtypes\Hidden::class,
        'html' => Fieldtypes\Html::class,
        'icon' => Fieldtypes\Icon::class,
        'info' => Fieldtypes\Info::class,
        'integer' => Fieldtypes\Integer::class,
        'link' => Fieldtypes\Link::class,
        'list' => Fieldtypes\Lists::class,
        'markdown' => Fieldtypes\Markdown::class,
        'markdown_buttons_setting' => Fieldtypes\Markdown\Buttons::class,
        'navs' => Fieldtypes\Navs::class,
        'fields' => Fieldtypes\NestedFields::class,
        'radio' => Fieldtypes\Radio::class,
        'range' => Fieldtypes\Range::class,
        'replicator' => Fieldtypes\Replicator::class,
        'revealer' => Fieldtypes\Revealer::class,
        'section' => Fieldtypes\Section::class,
        'select' => Fieldtypes\Select::class,
        'sets' => Fieldtypes\Sets::class,
        'sites' => Fieldtypes\Sites::class,
        'structures' => Fieldtypes\Structures::class,
        'slug' => Fieldtypes\Slug::class,
        'spacer' => Fieldtypes\Spacer::class,
        'table' => Fieldtypes\Table::class,
        'taggable' => Fieldtypes\Taggable::class,
        'terms' => Fieldtypes\Terms::class,
        'taxonomies' => Fieldtypes\Taxonomies::class,
        'template' => Fieldtypes\Template::class,
        'template_folder' => Fieldtypes\TemplateFolder::class,
        'text' => Fieldtypes\Text::class,
        'textarea' => Fieldtypes\Textarea::class,
        'theme' => Fieldtypes\Theme::class,
        'time' => Fieldtypes\Time::class,
        'toggle' => Fieldtypes\Toggle::class,
        'user_groups' => Fieldtypes\UserGroups::class,
        'user_roles' => Fieldtypes\UserRoles::class,
        'users' => Fieldtypes\Users::class,
        'width' => Fieldtypes\Width::class,
        'video' => Fieldtypes\Video::class,
        'yaml' => Fieldtypes\Yaml::class,
        'form' => \Statamic\Forms\Fieldtype::class,
    ];

    protected $modifierAliases = [
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
        'lowercase' => 'lower',
        'tz' => 'timezone',
        'inFuture' => 'isFuture',
        'inPast' => 'isPast',
        'as' => 'alias',
    ];

    protected $scopes = [
        'asset_properties' => Scopes\Filters\AssetProperties::class,
        'fields' => Scopes\Filters\Fields::class,
        'blueprint' => Scopes\Filters\Blueprint::class,
        'status' => Scopes\Filters\Status::class,
        'site' => Scopes\Filters\Site::class,
        'user_role' => Scopes\Filters\UserRole::class,
        'user_group' => Scopes\Filters\UserGroup::class,
        'collection' => Scopes\Filters\Collection::class,
    ];

    protected $tags = [
        'asset' => Tags\Asset::class,
        'assets' => Tags\Assets::class,
        'cache' => Tags\Cache::class,
        'can' => Tags\Can::class,
        'children' => Tags\Children::class,
        'component_proxy' => Tags\ComponentProxy::class,
        'collection' => Tags\Collection\Collection::class,
        'cookie' => Tags\Cookie::class,
        'dd' => Tags\Dd::class,
        'ddd' => Tags\Dd::class,
        'dictionary' => Tags\Dictionary\Dictionary::class,
        'dump' => Tags\Dump::class,
        'get_content' => Tags\GetContent::class,
        'get_error' => Tags\GetError::class,
        'get_errors' => Tags\GetErrors::class,
        'get_files' => Tags\GetFiles::class,
        'get_site' => Tags\GetSite::class,
        'glide' => Tags\Glide::class,
        'in' => Tags\In::class,
        'include' => Tags\IncludeTag::class,
        'increment' => Tags\Increment::class,
        'installed' => Tags\Installed::class,
        'is' => Tags\Is::class,
        'iterate' => Tags\Iterate::class,
        'foreach' => Tags\Iterate::class,
        'link' => Tags\Link::class,
        'locales' => Tags\Locales::class,
        'markdown' => Tags\Markdown::class,
        'member' => Tags\Member::class,
        'mix' => Tags\Mix::class,
        'mount_url' => Tags\MountUrl::class,
        'nav' => Tags\Nav::class,
        'not_found' => Tags\NotFound::class,
        '404' => Tags\NotFound::class,
        'obfuscate' => Tags\Obfuscate::class,
        'parent' => Tags\ParentTags::class,
        'partial' => Tags\Partial::class,
        'path' => Tags\Path::class,
        'query' => Tags\Query::class,
        'range' => Tags\Range::class,
        'loop' => Tags\Range::class,
        'redirect' => Tags\Redirect::class,
        'rotate' => Tags\Rotate::class,
        'switch' => Tags\Rotate::class,
        'route' => Tags\Route::class,
        'scope' => Tags\Scope::class,
        'set' => Tags\Set::class,
        'section' => Tags\Section::class,
        'session' => Tags\Session::class,
        'structure' => Tags\Structure::class,
        'svg' => Tags\Svg::class,
        'taxonomy' => Tags\Taxonomy\Taxonomy::class,
        'theme' => Tags\Theme::class,
        'trans' => Tags\Trans::class,
        'trans_choice' => Tags\TransChoice::class,
        'user_groups' => Tags\UserGroups::class,
        'users' => Tags\Users::class,
        'user_roles' => Tags\UserRoles::class,
        'vite' => Tags\Vite::class,
        'widont' => Tags\Widont::class,
        'yields' => Tags\Yields::class,
        'yield' => Tags\Yields::class,
        'form' => \Statamic\Forms\Tags::class,
        'user' => \Statamic\Auth\UserTags::class,
        'protect' => \Statamic\Auth\Protect\Tags::class,
        'oauth' => \Statamic\OAuth\Tags::class,
        'search' => \Statamic\Search\Tags::class,
        'nocache' => \Statamic\StaticCaching\NoCache\Tags::class,
    ];

    protected $widgets = [
        'collection' => Widgets\Collection::class,
        'template' => Widgets\Template::class,
        'updater' => Widgets\Updater::class,
        'form' => \Statamic\Forms\Widget::class,
    ];

    protected $formJsDrivers = [
        'alpine' => JsDrivers\Alpine::class,
        'alpine_precognition' => JsDrivers\AlpinePrecognition::class,
    ];

    protected $updateScripts = [
        Updates\AddPerEntryPermissions::class,
        Updates\UseDedicatedTrees::class,
        Updates\AddUniqueSlugValidation::class,
        Updates\AddGraphQLPermission::class,
        Updates\AddAssignRolesAndGroupsPermissions::class,
        Updates\AddDefaultPreferencesToGitConfig::class,
        Updates\AddConfigureFormFieldsPermission::class,
        Updates\AddSitePermissions::class,
        Updates\UseClassBasedStatamicUniqueRules::class,
        Updates\MigrateSitesConfigToYaml::class,
        Updates\AddTimezoneConfigOptions::class,
        Updates\RemoveParentField::class,
        Updates\UpdateGlobalVariables::class,
        Updates\PublishMigrationForTwoFactorColumns::class,
        Updates\PublishMigrationForWebauthnTable::class,
        Updates\AddAddonSettingsToGitConfig::class,
    ];

    public function register()
    {
        $this->registerExtensions();
        $this->registerAddonManifest();
        $this->registerFormJsDrivers();
        $this->registerUpdateScripts();
        $this->app->instance('statamic.hooks', collect());
    }

    public function boot()
    {
        Fieldtypes\Link::extend('entry', Fieldtypes\Link\EntryLinkType::class);
        Fieldtypes\Link::extend('asset', Fieldtypes\Link\AssetLinkType::class);
        Fieldtypes\Link::extend('term', Fieldtypes\Link\TermLinkType::class);
    }

    protected function registerAddonManifest()
    {
        $cachePath = $this->app->bootstrapPath().'/cache/addons.php';

        if (! is_null($env = Env::get('STATAMIC_ADDONS_CACHE'))) {
            $cachePath = Str::startsWith($env, ['/', '\\']) ? $env : $this->app->basePath($env);
        }

        $this->app->instance(Manifest::class, new Manifest(
            new Filesystem,
            $this->app->basePath(),
            $cachePath
        ));
    }

    protected function registerExtensions()
    {
        $this->app->instance('statamic.extensions', collect());

        $types = [
            'actions' => [
                'class' => Action::class,
                'directory' => 'Actions',
                'extensions' => $this->actions,
            ],
            'dictionaries' => [
                'class' => Dictionary::class,
                'directory' => 'Dictionaries',
                'extensions' => $this->dictionaries,
            ],
            'fieldtypes' => [
                'class' => Fieldtype::class,
                'directory' => 'Fieldtypes',
                'extensions' => $this->fieldtypes,
            ],
            'modifiers' => [
                'class' => Modifier::class,
                'directory' => 'Modifiers',
            ],
            'scopes' => [
                'class' => Scope::class,
                'directory' => 'Scopes',
                'extensions' => $this->scopes,
            ],
            'tags' => [
                'class' => Tags\Tags::class,
                'directory' => 'Tags',
                'extensions' => $this->tags,
            ],
            'widgets' => [
                'class' => Widget::class,
                'directory' => 'Widgets',
                'extensions' => $this->widgets,
            ],
        ];

        foreach ($types as $key => $type) {
            $this->registerBindingAlias($key, $type['class']);
            $this->registerCoreExtensions($type['class'], $type['extensions'] ?? []);
            $this->registerAppExtensions($type['directory'], $type['class']);
        }

        $this->registerCoreModifiers();
    }

    protected function registerBindingAlias($key, $class)
    {
        return $this->app->bind('statamic.'.$key, function ($app) use ($class) {
            return $app['statamic.extensions'][$class];
        });
    }

    protected function registerCoreExtensions($class, $extensions)
    {
        $this->app['statamic.extensions'][$class] = collect($extensions);
    }

    protected function registerAppExtensions($folder, $requiredClass)
    {
        if (! $this->app['files']->exists($path = app_path($folder))) {
            return;
        }

        foreach ($this->app['files']->allFiles($path) as $file) {
            $relativePathOfFolder = str_replace(app_path(DIRECTORY_SEPARATOR), '', $file->getPath());
            $namespace = str_replace('/', '\\', $relativePathOfFolder);
            $class = $file->getBasename('.php');

            $fqcn = $this->app->getNamespace()."{$namespace}\\{$class}";
            if (is_subclass_of($fqcn, $requiredClass)) {
                $fqcn::register();
            }
        }
    }

    protected function registerCoreModifiers()
    {
        $modifiers = collect();
        $methods = array_diff(get_class_methods(CoreModifiers::class), get_class_methods(Modifier::class));

        foreach ($methods as $method) {
            $modifiers[Str::snake($method)] = CoreModifiers::class.'@'.$method;
        }

        foreach ($this->modifierAliases as $alias => $actual) {
            $modifiers[$alias] = CoreModifiers::class.'@'.$actual;
        }

        $this->app['statamic.extensions'][Modifier::class] = collect()
            ->merge($this->app['statamic.extensions'][Modifier::class] ?? [])
            ->merge($modifiers);
    }

    protected function registerFormJsDrivers()
    {
        $this->app->instance('statamic.form-js-drivers', collect($this->formJsDrivers));
    }

    protected function registerUpdateScripts()
    {
        $this->app->instance('statamic.update-scripts', collect($this->updateScripts)->map(fn ($class) => [
            'class' => $class,
            'package' => Statamic::PACKAGE,
        ]));
    }
}

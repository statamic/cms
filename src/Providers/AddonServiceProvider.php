<?php

namespace Statamic\Providers;

use Closure;
use Illuminate\Console\Command;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Reflector;
use Illuminate\Support\ServiceProvider;
use Statamic\Actions\Action;
use Statamic\Dictionaries\Dictionary;
use Statamic\Exceptions\NotBootedException;
use Statamic\Extend\Manifest;
use Statamic\Facades\Addon;
use Statamic\Facades\Blueprint;
use Statamic\Facades\Fieldset;
use Statamic\Facades\Path;
use Statamic\Fields\Fieldtype;
use Statamic\Forms\JsDrivers\JsDriver;
use Statamic\Modifiers\Modifier;
use Statamic\Query\Scopes\Scope;
use Statamic\Statamic;
use Statamic\Support\Arr;
use Statamic\Support\Str;
use Statamic\Tags\Tags;
use Statamic\UpdateScripts\UpdateScript;
use Statamic\Widgets\Widget;

abstract class AddonServiceProvider extends ServiceProvider
{
    /**
     * Array of event class => Listener class.
     *
     * @var array<class-string, class-string[]>
     */
    protected $listen = [];

    /**
     * @var list<class-string>
     */
    protected $subscribe = [];

    /**
     * @var list<class-string<Tags>>
     */
    protected $tags = [];

    /**
     * @var list<class-string<Scope>>
     */
    protected $scopes = [];

    /**
     * @var list<class-string<Action>>
     */
    protected $actions = [];

    /**
     * @var list<class-string<Dictionary>>
     */
    protected $dictionaries = [];

    /**
     * @var list<class-string<Fieldtype>>
     */
    protected $fieldtypes = [];

    /**
     * @var list<class-string<Modifier>>
     */
    protected $modifiers = [];

    /**
     * @var list<class-string<Widget>>
     */
    protected $widgets = [];

    /**
     * @var list<class-string<JsDriver>>
     */
    protected $formJsDrivers = [];

    /**
     * @var array<class-string, string>
     */
    protected $policies = [];

    /**
     * @var list<class-string<Command>>
     */
    protected $commands = [];

    /**
     * @var list<string> - Paths on disk
     */
    protected $stylesheets = [];

    /**
     * @var list<string> - URLs of stylesheets
     */
    protected $externalStylesheets = [];

    /**
     * @var list<string> - Paths on disk
     */
    protected $scripts = [];

    /**
     * @var list<string> - URLs of scripts
     */
    protected $externalScripts = [];

    /**
     * @var list<string> - URLs of Vite entry points
     */
    protected $vite = null;

    /**
     * Map of path on disk to name in the public directory. The file will be published
     * as `vendor/{packageName}/{value}`.
     *
     * @var array<string, string>
     */
    protected $publishables = [];

    /**
     * Map of type => Path of route PHP file on disk where the key (type) can be one
     * of `cp`, `web`, `actions`.
     *
     * @template TType of 'cp'|'web'|'actions'
     *
     * @var array<TType, string>
     */
    protected $routes = [];

    /**
     * @var string|null
     */
    protected $routeNamespace;

    /**
     * Map of group name => Middlewares to apply.
     *
     * @var array<string, class-string[]>
     */
    protected $middlewareGroups = [];

    /**
     * @var list<class-string<UpdateScript>>
     */
    protected $updateScripts = [];

    /**
     * @var string
     */
    protected $blueprintNamespace;

    /**
     * @var string
     */
    protected $fieldsetNamespace;

    /**
     * @var string
     */
    protected $viewNamespace;

    /**
     * @var bool
     */
    protected $publishAfterInstall = true;

    /**
     * @var bool
     */
    protected $config = true;

    /**
     * @var bool
     */
    protected $translations = true;

    private $autoloadedClasses;

    private $bootedAddons;

    public function boot()
    {
        Statamic::booted(function () {
            if (! $this->getAddon()) {
                return;
            }

            $this
                ->bootEvents()
                ->bootTags()
                ->bootScopes()
                ->bootActions()
                ->bootDictionaries()
                ->bootFieldtypes()
                ->bootModifiers()
                ->bootWidgets()
                ->bootFormJsDrivers()
                ->bootCommands()
                ->bootSchedule()
                ->bootPolicies()
                ->bootStylesheets()
                ->bootScripts()
                ->bootVite()
                ->bootPublishables()
                ->bootConfig()
                ->bootTranslations()
                ->bootRoutes()
                ->bootMiddleware()
                ->bootUpdateScripts()
                ->bootViews()
                ->bootBlueprints()
                ->bootFieldsets()
                ->bootPublishAfterInstall()
                ->bootAddon();

            $this->bootedAddons()->push($this->getAddon()->id());
        });
    }

    public function bootAddon()
    {
        //
    }

    public function bootEvents()
    {
        $this->getEventListeners()->each(function ($listeners, $event) {
            foreach ($listeners as $listener) {
                Event::listen($event, $listener);
            }
        });

        $subscribers = collect($this->subscribe)
            ->merge($this->autoloadFilesFromFolder('Subscribers'))
            ->unique();

        foreach ($subscribers as $subscriber) {
            Event::subscribe($subscriber);
        }

        return $this;
    }

    private function getEventListeners()
    {
        $arr = [];

        foreach ($this->discoverListenerEvents() as $listener => $events) {
            foreach ($events as $event) {
                $arr[$event][] = $listener;
            }
        }

        foreach ($this->listen as $event => $listeners) {
            foreach ($listeners as $listener) {
                if (! in_array($listener, $arr[$event] ?? [])) {
                    $arr[$event][] = $listener;
                }
            }
        }

        return collect($arr);
    }

    private function discoverListenerEvents()
    {
        return collect($this->autoloadFilesFromFolder('Listeners'))->mapWithKeys(function ($class) {
            $listener = new \ReflectionClass($class);
            $events = [];

            foreach ($listener->getMethods(\ReflectionMethod::IS_PUBLIC) as $method) {
                if ((! Str::is('handle*', $method->name) && ! Str::is('__invoke', $method->name)) || ! isset($method->getParameters()[0])) {
                    continue;
                }

                $key = Str::is(['__invoke', 'handle'], $method->name) ? $class : $class.'@'.$method->name;

                $events[$key] = Reflector::getParameterClassNames($method->getParameters()[0]);
            }

            return $events;
        });
    }

    protected function bootTags()
    {
        $tags = collect($this->tags)
            ->merge($this->autoloadFilesFromFolder('Tags', Tags::class))
            ->unique();

        foreach ($tags as $class) {
            $class::register();
        }

        return $this;
    }

    protected function bootScopes()
    {
        $scopes = collect($this->scopes)
            ->merge($this->autoloadFilesFromFolder('Scopes', Scope::class))
            ->merge($this->autoloadFilesFromFolder('Query/Scopes', Scope::class))
            ->merge($this->autoloadFilesFromFolder('Query/Scopes/Filters', Scope::class))
            ->unique();

        foreach ($scopes as $class) {
            $class::register();
        }

        return $this;
    }

    protected function bootActions()
    {
        $actions = collect($this->actions)
            ->merge($this->autoloadFilesFromFolder('Actions', Action::class))
            ->unique();

        foreach ($actions as $class) {
            $class::register();
        }

        return $this;
    }

    protected function bootDictionaries()
    {
        $dictionaries = collect($this->dictionaries)
            ->merge($this->autoloadFilesFromFolder('Dictionaries', Dictionary::class))
            ->unique();

        foreach ($dictionaries as $class) {
            $class::register();
        }

        return $this;
    }

    protected function bootFieldtypes()
    {
        $fieldtypes = collect($this->fieldtypes)
            ->merge($this->autoloadFilesFromFolder('Fieldtypes', Fieldtype::class))
            ->unique();

        foreach ($fieldtypes as $class) {
            $class::register();
        }

        return $this;
    }

    protected function bootModifiers()
    {
        $modifiers = collect($this->modifiers)
            ->merge($this->autoloadFilesFromFolder('Modifiers', Modifier::class))
            ->unique();

        foreach ($modifiers as $class) {
            $class::register();
        }

        return $this;
    }

    protected function bootWidgets()
    {
        $widgets = collect($this->widgets)
            ->merge($this->autoloadFilesFromFolder('Widgets', Widget::class))
            ->unique();

        foreach ($widgets as $class) {
            $class::register();
        }

        return $this;
    }

    protected function bootFormJsDrivers()
    {
        foreach ($this->formJsDrivers as $class) {
            $class::register();
        }

        return $this;
    }

    protected function bootPolicies()
    {
        foreach ($this->policies as $key => $value) {
            Gate::policy($key, $value);
        }

        return $this;
    }

    protected function bootCommands()
    {
        if ($this->app->runningInConsole()) {
            $commands = collect($this->commands)
                ->merge($this->autoloadFilesFromFolder('Commands', Command::class))
                ->merge($this->autoloadFilesFromFolder('Console/Commands', Command::class))
                ->unique()
                ->all();

            $this->commands($commands);
        }

        return $this;
    }

    protected function bootSchedule()
    {
        if ($this->app->runningInConsole()) {
            $this->schedule($this->app->make(Schedule::class));
        }

        return $this;
    }

    protected function bootStylesheets()
    {
        foreach ($this->stylesheets as $path) {
            $this->registerStylesheet($path);
        }

        foreach ($this->externalStylesheets as $url) {
            $this->registerExternalStylesheet($url);
        }

        return $this;
    }

    protected function bootScripts()
    {
        foreach ($this->scripts as $path) {
            $this->registerScript($path);
        }

        foreach ($this->externalScripts as $url) {
            $this->registerExternalScript($url);
        }

        return $this;
    }

    protected function bootVite()
    {
        if ($this->vite) {
            $this->registerVite($this->vite);
        }

        return $this;
    }

    protected function bootConfig()
    {
        if (! $this->shouldBootRootItems()) {
            return $this;
        }

        $filename = $this->getAddon()->slug();
        $directory = $this->getAddon()->directory();
        $origin = "{$directory}config/{$filename}.php";

        if (! $this->config || ! file_exists($origin)) {
            return $this;
        }

        $this->mergeConfigFrom($origin, $filename);

        $this->publishes([
            $origin => config_path("{$filename}.php"),
        ], "{$filename}-config");

        return $this;
    }

    protected function bootTranslations()
    {
        if (! $this->shouldBootRootItems()) {
            return $this;
        }

        $slug = $this->getAddon()->slug();
        $directory = $this->getAddon()->directory();
        $origin = "{$directory}lang";

        // Support older Laravel lang path convention within addons as well.
        if (! file_exists($origin)) {
            $origin = "{$directory}resources/lang";
        }

        if (! $this->translations || ! file_exists($origin)) {
            return $this;
        }

        $this->loadTranslationsFrom($origin, $slug);

        $this->publishes([
            $origin => app()->langPath()."/vendor/{$slug}",
        ], "{$slug}-translations");

        return $this;
    }

    protected function bootPublishables()
    {
        $package = $this->getAddon()->packageName();

        $publishables = collect($this->publishables)
            ->mapWithKeys(function ($destination, $origin) use ($package) {
                return [$origin => public_path("vendor/{$package}/{$destination}")];
            });

        if ($publishables->isNotEmpty()) {
            $this->publishes($publishables->all(), $this->getAddon()->slug());
        }

        return $this;
    }

    protected function bootRoutes()
    {
        $directory = $this->getAddon()->directory();

        $web = Arr::get(
            array: $this->routes,
            key: 'web',
            default: $this->shouldBootRootItems() && $this->app['files']->exists($path = $directory.'routes/web.php') ? $path : null
        );

        if ($web) {
            $this->registerWebRoutes($web);
        }

        $cp = Arr::get(
            array: $this->routes,
            key: 'cp',
            default: $this->shouldBootRootItems() && $this->app['files']->exists($path = $directory.'routes/cp.php') ? $path : null
        );

        if ($cp) {
            $this->registerCpRoutes($cp);
        }

        $actions = Arr::get(
            array: $this->routes,
            key: 'actions',
            default: $this->shouldBootRootItems() && $this->app['files']->exists($path = $directory.'routes/actions.php') ? $path : null
        );

        if ($actions) {
            $this->registerActionRoutes($actions);
        }

        return $this;
    }

    protected function routeNamespace()
    {
        return $this->routeNamespace;
    }

    /**
     * Register routes from the root of the site.
     *
     * @param  string|Closure  $routes  Either the path to a routes file, or a closure containing routes.
     * @return void
     */
    public function registerWebRoutes($routes)
    {
        Statamic::pushWebRoutes(function () use ($routes) {
            Route::namespace($this->routeNamespace())->group($routes);
        });
    }

    /**
     * Register routes scoped to the addon's section in the Control Panel.
     *
     * @param  string|Closure  $routes  Either the path to a routes file, or a closure containing routes.
     * @return void
     */
    public function registerCpRoutes($routes)
    {
        Statamic::pushCpRoutes(function () use ($routes) {
            Route::namespace($this->routeNamespace())->group($routes);
        });
    }

    /**
     * Register routes scoped to the addon's front-end actions.
     *
     * @param  string|Closure  $routes  Either the path to a routes file, or a closure containing routes.
     * @return void
     */
    public function registerActionRoutes($routes)
    {
        Statamic::pushActionRoutes(function () use ($routes) {
            Route::namespace($this->routeNamespace())
                ->prefix($this->getAddon()->slug())
                ->group($routes);
        });
    }

    protected function bootMiddleware()
    {
        foreach ($this->middlewareGroups as $group => $middleware) {
            foreach ($middleware as $class) {
                $this->app['router']->pushMiddlewareToGroup($group, $class);
            }
        }

        return $this;
    }

    protected function bootUpdateScripts()
    {
        $scripts = collect($this->updateScripts)
            ->merge($this->autoloadFilesFromFolder('UpdateScripts', UpdateScript::class))
            ->unique();

        foreach ($scripts as $class) {
            $class::register($this->getAddon()->package());
        }

        return $this;
    }

    protected function bootViews()
    {
        if (! $this->shouldBootRootItems()) {
            return $this;
        }

        if (file_exists($this->getAddon()->directory().'resources/views')) {
            $this->loadViewsFrom(
                $this->getAddon()->directory().'resources/views',
                $this->viewNamespace ?? $this->getAddon()->packageName()
            );
        }

        return $this;
    }

    public function registerScript(string $path)
    {
        $name = $this->getAddon()->packageName();
        $version = $this->getAddon()->version();
        $filename = pathinfo($path, PATHINFO_FILENAME);

        $this->publishes([
            $path => public_path("vendor/{$name}/js/{$filename}.js"),
        ], $this->getAddon()->slug());

        Statamic::script($name, "{$filename}.js?v=".md5($version));
    }

    public function registerVite($config)
    {
        $name = $this->getAddon()->packageName();
        $directory = $this->getAddon()->directory();

        if (is_string($config) || ! Arr::isAssoc($config)) {
            $config = ['input' => $config];
        }

        $publicDirectory = $config['publicDirectory'] ?? 'public';
        $buildDirectory = $config['buildDirectory'] ?? 'build';
        $hotFile = $config['hotFile'] ?? "{$directory}{$publicDirectory}/hot";
        $input = $config['input'];

        $publishSource = "{$directory}{$publicDirectory}/{$buildDirectory}/";
        $publishTarget = public_path("vendor/{$name}/{$buildDirectory}/");
        $this->publishes([
            $publishSource => $publishTarget,
        ], $this->getAddon()->slug());

        Statamic::vite($name, [
            'hotFile' => $hotFile,
            'buildDirectory' => "vendor/{$name}/{$buildDirectory}",
            'input' => $input,
        ]);
    }

    public function registerExternalScript(string $url)
    {
        Statamic::externalScript($url);
    }

    public function registerStylesheet(string $path)
    {
        $name = $this->getAddon()->packageName();
        $version = $this->getAddon()->version();
        $filename = pathinfo($path, PATHINFO_FILENAME);

        $this->publishes([
            $path => public_path("vendor/{$name}/css/{$filename}.css"),
        ], $this->getAddon()->slug());

        Statamic::style($name, "{$filename}.css?v=".md5($version));
    }

    public function registerExternalStylesheet(string $url)
    {
        Statamic::externalStyle($url);
    }

    protected function schedule(Schedule $schedule)
    {
        //
    }

    protected function namespace()
    {
        return $this->getAddon()->namespace();
    }

    protected function getAddon()
    {
        throw_unless($this->app->isBooted(), new NotBootedException);

        if (! $addon = $this->getAddonByServiceProvider()) {
            // No addon? Then we're trying to boot one that hasn't been discovered yet.
            // Probably just installed and we're inside the statamic:install command.
            $this->app[Manifest::class]->build();
            $addon = $this->getAddonByServiceProvider();
        }

        return $addon;
    }

    private function getAddonByServiceProvider()
    {
        return Addon::all()->first(function ($addon) {
            return Str::startsWith(get_class($this), $addon->namespace().'\\');
        });
    }

    protected function bootPublishAfterInstall()
    {
        if (! $this->publishAfterInstall) {
            return $this;
        }

        if (empty($this->scripts) && empty($this->stylesheets) && empty($this->vite) && empty($this->publishables)) {
            return $this;
        }

        Statamic::afterInstalled(function ($command) {
            $command->call('vendor:publish', [
                '--tag' => $this->getAddon()->slug(),
                '--force' => true,
            ]);
        });

        return $this;
    }

    protected function bootBlueprints()
    {
        if (! $this->shouldBootRootItems()) {
            return $this;
        }

        if (! file_exists($path = "{$this->getAddon()->directory()}resources/blueprints")) {
            return $this;
        }

        Blueprint::addNamespace(
            $this->blueprintNamespace ?? $this->getAddon()->slug(),
            $path
        );

        return $this;
    }

    protected function bootFieldsets()
    {
        if (! $this->shouldBootRootItems()) {
            return $this;
        }

        if (! file_exists($path = "{$this->getAddon()->directory()}resources/fieldsets")) {
            return $this;
        }

        Fieldset::addNamespace(
            $this->fieldsetNamespace ?? $this->getAddon()->slug(),
            $path
        );

        return $this;
    }

    protected function autoloadFilesFromFolder($folder, $requiredClass = null)
    {
        try {
            $addon = $this->getAddon();
        } catch (NotBootedException $e) {
            // This would be thrown if a developer has tried to call a method
            // that triggers autoloading before Statamic has booted. Perhaps
            // they have placed it in the boot method instead of bootAddon.
            return [];
        }

        $reflection = new \ReflectionClass(static::class);
        $path = dirname($reflection->getFileName()).'/'.$folder;

        if (! $this->app['files']->exists($path)) {
            return [];
        }

        $autoloadable = [];

        foreach ($this->app['files']->files($path) as $file) {
            if ($file->getExtension() !== 'php') {
                continue;
            }

            $class = $file->getBasename('.php');
            $fqcn = $reflection->getNamespaceName().'\\'.str_replace('/', '\\', $folder).'\\'.$class;

            if ((new \ReflectionClass($fqcn))->isAbstract() || (new \ReflectionClass($fqcn))->isInterface()) {
                continue;
            }

            if ($requiredClass && ! is_subclass_of($fqcn, $requiredClass)) {
                continue;
            }

            if ($this->autoloadedClasses()->contains($fqcn)) {
                continue;
            }

            $autoloadable[] = $fqcn;
            $this->autoloadedClasses()->push($fqcn);
        }

        return $autoloadable;
    }

    private function shouldBootRootItems()
    {
        $addon = $this->getAddon();

        // We'll keep track of addons that have been booted to ensure that multiple
        // providers don't try to boot things twice. This could happen if there are
        // multiple providers in the root autoload directory (src) of an addon.
        if ($this->bootedAddons()->contains($addon->id())) {
            return false;
        }

        // We only want to boot root items if the provider is in the autoloaded directory.
        // i.e. It's the "root" provider. If it's in a subdirectory maybe the developer
        // is organizing their providers. Things like tags etc. can be autoloaded but
        // root level things like routes, views, config, blueprints, etc. will not.
        $thisDir = Path::tidy(dirname((new \ReflectionClass(static::class))->getFileName()));
        $autoloadDir = $addon->directory().$addon->autoload();

        return $thisDir === $autoloadDir;
    }

    private function autoloadedClasses()
    {
        if ($this->autoloadedClasses) {
            return $this->autoloadedClasses;
        }

        if (! $this->app->bound($autoloaded = 'statamic.autoloaded-addon-classes')) {
            $this->app->instance($autoloaded, collect());
        }

        return $this->autoloadedClasses = $this->app->make($autoloaded);
    }

    private function bootedAddons()
    {
        if ($this->bootedAddons) {
            return $this->bootedAddons;
        }

        if (! $this->app->bound($booted = 'statamic.booted-addons')) {
            $this->app->instance($booted, collect());
        }

        return $this->bootedAddons = $this->app->make($booted);
    }
}

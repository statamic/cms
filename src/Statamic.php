<?php

namespace Statamic;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Cache;
use Laravel\Nova\Nova;
use Statamic\Facades\File;
use Statamic\Facades\Preference;
use Statamic\Facades\Site;
use Statamic\Facades\URL;
use Statamic\Modifiers\Modify;
use Statamic\Support\Arr;
use Statamic\Support\DateFormat;
use Statamic\Support\Str;
use Statamic\Tags\FluentTag;
use Stringy\StaticStringy;

class Statamic
{
    const CORE_SLUG = 'statamic';
    const PACKAGE = 'statamic/cms';

    protected static $scripts = [];
    protected static $externalScripts = [];
    protected static $styles = [];
    protected static $externalStyles = [];
    protected static $vites = [];
    protected static $cpRoutes = [];
    protected static $webRoutes = [];
    protected static $actionRoutes = [];
    protected static $jsonVariables = [];
    protected static $bootedCallbacks = [];
    protected static $afterInstalledCallbacks = [];

    public static function version()
    {
        return \Facades\Statamic\Version::get();
    }

    public static function pro()
    {
        return config('statamic.editions.pro');
    }

    public static function enablePro()
    {
        $path = config_path('statamic/editions.php');

        $contents = File::get($path);

        if (! Str::contains($contents, "'pro' => false,")) {
            throw new \Exception('Could not reliably update the config file.');
        }

        $contents = str_replace("'pro' => false,", "'pro' => true,", $contents);

        File::put($path, $contents);
    }

    public static function availableScripts(Request $request)
    {
        return static::$scripts;
    }

    public static function availableExternalScripts(Request $request)
    {
        return static::$externalScripts;
    }

    public static function script($name, $path)
    {
        static::$scripts[$name][] = self::createVersionedAssetPath($name, $path, 'js');

        return new static;
    }

    public static function externalScript($url)
    {
        static::$externalScripts[] = $url;

        return new static;
    }

    public static function availableStyles(Request $request)
    {
        return static::$styles;
    }

    public static function availableExternalStyles(Request $request)
    {
        return static::$externalStyles;
    }

    public static function style($name, $path)
    {
        static::$styles[$name][] = self::createVersionedAssetPath($name, $path, 'css');

        return new static;
    }

    public static function externalStyle($url)
    {
        static::$externalStyles[] = $url;

        return new static;
    }

    public static function vite($name, $config)
    {
        if (is_string($config) || ! Arr::isAssoc($config)) {
            $config = ['input' => $config];
        }

        static::$vites[$name] = array_merge([
            'hotFile' => null,
            'buildDirectory' => 'build',
        ], $config);

        return new static;
    }

    public static function availableVites(Request $request)
    {
        return static::$vites;
    }

    public static function pushWebRoutes(Closure $routes)
    {
        static::$webRoutes[] = $routes;

        return new static;
    }

    public static function pushCpRoutes(Closure $routes)
    {
        static::$cpRoutes[] = $routes;

        return new static;
    }

    public static function pushActionRoutes(Closure $routes)
    {
        static::$actionRoutes[] = $routes;

        return new static;
    }

    public static function additionalCpRoutes()
    {
        foreach (static::$cpRoutes as $routes) {
            $routes();
        }
    }

    public static function additionalWebRoutes()
    {
        foreach (static::$webRoutes as $routes) {
            $routes();
        }
    }

    public static function additionalActionRoutes()
    {
        foreach (static::$actionRoutes as $routes) {
            $routes();
        }
    }

    public static function isCpRoute()
    {
        if (! config('statamic.cp.enabled')) {
            return false;
        }

        $cp = config('statamic.cp.route');
        $path = request()->path();

        return $path === $cp
            || Str::startsWith($path, Str::finish($cp, '/'));
    }

    public static function cpRoute($route, $params = [])
    {
        if (! config('statamic.cp.enabled')) {
            return null;
        }

        $route = route('statamic.cp.'.$route, $params);

        // TODO: This is a temporary workaround to routes like
        // `route('assets.browse.edit', 'some/image.jpg')` outputting two slashes.
        // Can it be fixed with route regex, or is it a laravel bug?
        $route = preg_replace('/(?<!:)\/\//', '/', $route);

        return $route;
    }

    public static function isApiRoute()
    {
        if (! config('statamic.api.enabled') || ! static::pro()) {
            return false;
        }

        return starts_with(request()->path(), config('statamic.api.route'));
    }

    public static function apiRoute($route, $params = [])
    {
        if (! config('statamic.api.enabled') || ! static::pro()) {
            return null;
        }

        $route = route('statamic.api.'.$route, $params);

        // TODO: This is a temporary workaround to routes like
        // `route('assets.browse.edit', 'some/image.jpg')` outputting two slashes.
        // Can it be fixed with route regex, or is it a laravel bug?
        $route = preg_replace('/(?<!:)\/\//', '/', $route);

        return $route;
    }

    public static function isAmpRequest()
    {
        if (! config('statamic.amp.enabled')) {
            return false;
        }

        $url = Site::current()->relativePath(
            str_finish(request()->getUri(), '/')
        );

        return starts_with($url, '/'.config('statamic.amp.route'));
    }

    public static function jsonVariables(Request $request)
    {
        return collect(static::$jsonVariables)->map(function ($variable) use ($request) {
            return is_callable($variable) && ! is_string($variable) ? $variable($request) : $variable;
        })->all();
    }

    public static function provideToScript(array $variables)
    {
        static::$jsonVariables = array_merge(static::$jsonVariables, $variables);

        return new static;
    }

    public static function svg($name, $attrs = null)
    {
        if ($attrs) {
            $attrs = " class=\"{$attrs}\"";
        }

        $svg = StaticStringy::collapseWhitespace(
            File::get(statamic_path("resources/svg/{$name}.svg"))
        );

        return str_replace('<svg', sprintf('<svg%s', $attrs), $svg);
    }

    public static function vendorAssetUrl($url = '/')
    {
        return asset(URL::tidy('vendor/'.$url));
    }

    public static function vendorPackageAssetUrl($package, $url = null, $type = null)
    {
        // If a vendor URL has already been provided, bypass the rest of the logic.
        if (Str::startsWith($url, ['vendor', '/vendor'])) {
            return self::vendorAssetUrl(Str::after($url, 'vendor/'));
        }

        return self::vendorAssetUrl($package.'/'.$type.'/'.$url);
    }

    public static function cpAssetUrl($url = '/')
    {
        return static::vendorPackageAssetUrl('statamic/cp', $url);
    }

    public static function cpDateFormat()
    {
        return Preference::get('date_format', config('statamic.cp.date_format'));
    }

    public static function cpDateTimeFormat()
    {
        $format = self::cpDateFormat();

        return DateFormat::containsTime($format) ? $format : $format.' H:i';
    }

    public static function dateFormat()
    {
        return config('statamic.system.date_format');
    }

    public static function dateTimeFormat()
    {
        $format = self::dateFormat();

        return DateFormat::containsTime($format) ? $format : $format.' H:i';
    }

    public static function flash()
    {
        if ($success = session('success')) {
            $messages[] = ['type' => 'success', 'message' => $success];
        }

        if ($error = session('error')) {
            $messages[] = ['type' => 'error', 'message' => $error];
        }

        if ($info = session('info')) {
            $messages[] = ['type' => 'info', 'message' => $info];
        }

        return $messages ?? [];
    }

    public static function crumb(...$values)
    {
        return implode(' ‹ ', array_map(fn ($str) => Statamic::trans($str), $values));
    }

    public static function docsUrl($url)
    {
        return URL::tidy('https://statamic.dev/'.$url);
    }

    public static function booted(Closure $callback)
    {
        static::$bootedCallbacks[] = $callback;
    }

    public static function runBootedCallbacks()
    {
        foreach (static::$bootedCallbacks as $callback) {
            $callback();
        }

        static::$bootedCallbacks = [];
    }

    public static function afterInstalled(Closure $callback)
    {
        static::$afterInstalledCallbacks[] = $callback;
    }

    public static function runAfterInstalledCallbacks($command)
    {
        foreach (static::$afterInstalledCallbacks as $callback) {
            $callback($command);
        }

        static::$afterInstalledCallbacks = [];
    }

    public static function repository($abstract, $concrete)
    {
        app()->singleton($abstract, $concrete);

        foreach ($concrete::bindings() as $abstract => $concrete) {
            app()->bind($abstract, $concrete);
        }
    }

    public static function frontendRouteSegmentRegex()
    {
        $prefix = '';

        if (class_exists(Nova::class)) {
            $prefix = '(?!'.trim(Nova::path(), '/').')';
        }

        return $prefix.'.*';
    }

    public static function tag($name)
    {
        return FluentTag::make($name);
    }

    public static function modify($value)
    {
        return Modify::value($value);
    }

    public static function query($name)
    {
        return app()->make('statamic.queries.'.$name);
    }

    public static function trans($key, $replace = [], $locale = null)
    {
        $line = __($key, $replace, $locale);

        if (is_array($line)) {
            return $key;
        }

        return $line;
    }

    public static function isWorker()
    {
        if (! App::runningInConsole()) {
            return false;
        }

        return Str::startsWith(Arr::get(request()->server(), 'argv.1') ?? '', ['queue:', 'horizon:']);
    }

    private static function createVersionedAssetPath($name, $path, $extension)
    {
        // If passing a path versioned by laravel mix, it will contain ?id=
        // Do nothing and return that path.
        if (Str::contains($path, '?id=')) {
            return (string) $path;
        }

        return Cache::rememberForever("statamic-{$extension}-{$name}-{md5($path)}", function () use ($path, $extension) {
            // In case a file without any version will be passed,
            // a random version number will be created.
            if (! Str::contains($path, '?v=')) {
                $version = str_random();

                // Add the file extension if not provided.
                $path = str_finish($path, ".{$extension}");

                // Add the version to the path.
                $path = str_finish($path, "?v={$version}");
            }

            return $path;
        });
    }
}

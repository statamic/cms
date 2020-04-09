<?php

namespace Statamic;

use Closure;
use Illuminate\Http\Request;
use Statamic\Facades\File;
use Statamic\Facades\Site;
use Statamic\Facades\URL;
use Statamic\Facades\User;
use Statamic\Http\Middleware\CP\Authorize;
use Statamic\Http\Middleware\Localize as LocalizeFrontend;
use Statamic\Http\Middleware\CP\Localize as LocalizeCp;
use Statamic\StaticCaching\Middleware\Cache;
use Stringy\StaticStringy;

class Statamic
{
    const CORE_SLUG = 'statamic';
    const CORE_REPO = 'statamic/cms';

    protected static $scripts = [];
    protected static $externalScripts = [];
    protected static $styles = [];
    protected static $cpRoutes = [];
    protected static $webRoutes = [];
    protected static $actionRoutes = [];
    protected static $jsonVariables = [];
    protected static $webMiddleware = [
        LocalizeFrontend::class,
        Cache::class,
    ];
    protected static $cpMiddleware = [
        Authorize::class,
        LocalizeCp::class,
    ];

    public static function version()
    {
        return \Facades\Statamic\Version::get();
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
        static::$scripts[$name] = str_finish($path, '.js');

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

    public static function style($name, $path)
    {
        static::$styles[$name] = str_finish($path, '.css');

        return new static;
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

        return starts_with(request()->path(), config('statamic.cp.route'));
    }

    public static function cpRoute($route, $params = [])
    {
        if (! config('statamic.cp.enabled')) {
            return null;
        }

        $route = route('statamic.cp.' . $route, $params);

        // TODO: This is a temporary workaround to routes like
        // `route('assets.browse.edit', 'some/image.jpg')` outputting two slashes.
        // Can it be fixed with route regex, or is it a laravel bug?
        $route = preg_replace('/(?<!:)\/\//', '/', $route);

        return $route;
    }

    public static function isApiRoute()
    {
        if (! config('statamic.api.enabled')) {
            return false;
        }

        return starts_with(request()->path(), config('statamic.api.route'));
    }

    public static function apiRoute($route, $params = [])
    {
        if (! config('statamic.api.enabled')) {
            return null;
        }

        $route = route('statamic.api.' . $route, $params);

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

        return starts_with($url, '/' . config('statamic.amp.route'));
    }

    public static function jsonVariables(Request $request)
    {
        return collect(static::$jsonVariables)->map(function ($variable) use ($request) {
            return is_callable($variable) ? $variable($request) : $variable;
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
            File::get(statamic_path("resources/dist/svg/{$name}.svg"))
        );

        return str_replace('<svg', sprintf('<svg%s', $attrs), $svg);
    }

    public static function vendorAssetUrl($url = '/')
    {
        return asset(URL::tidy('vendor/' . $url));
    }

    public static function cpAssetUrl($url = '/')
    {
        return static::vendorAssetUrl('statamic/cp/' . $url);
    }

    public static function flash()
    {
        if ($success = session('success')) {
            $messages[] = ['type' => 'success', 'message' => $success];
        }

        if ($error = session('error')) {
            $messages[] = ['type' => 'error', 'message' => $error];
        }

        return $messages ?? [];
    }

    public static function crumb(...$values)
    {
        return implode(' ‹ ', array_map("__", $values));
    }

    public static function cpMiddleware()
    {
        return static::$cpMiddleware;
    }

    public static function webMiddleware()
    {
        return static::$webMiddleware;
    }

    public static function pushCpMiddleware($middleware)
    {
        static::$cpMiddleware[] = $middleware;
    }

    public static function pushWebMiddleware($middleware)
    {
        static::$webMiddleware[] = $middleware;
    }

    public static function docsUrl($url)
    {
        return URL::tidy('https://statamic.dev/' . $url);
    }
}

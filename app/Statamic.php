<?php

namespace Statamic;

use Closure;
use Facades\Statamic\Console\Processes\Composer;
use Illuminate\Http\Request;

class Statamic
{
    const CORE_REPO = 'test/package'; // Will be `statamic/cms` on release.

    protected static $scripts = [];
    protected static $styles = [];
    protected static $cpRoutes = [];
    protected static $webRoutes = [];
    protected static $actionRoutes = [];
    protected static $jsonVariables = [];

    public static function version()
    {
        if (app()->environment() === 'testing') {
            return '3.0.0-testing';
        }

        return Composer::installedVersion(static::CORE_REPO);
    }

    public static function availableScripts(Request $request)
    {
        return static::$scripts;
    }

   public static function script($name, $path)
   {
       static::$scripts[$name] = str_finish($path, '.js');

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

    public static function jsonVariables(Request $request)
    {
        return collect(static::$jsonVariables)->map(function ($variable) use ($request) {
            return is_callable($variable) ? $variable($request) : $variable;
        })->all();
    }

    public static function provideToScript(array $variables)
    {
        if (empty(static::$jsonVariables)) {
            static::$jsonVariables = [
                'version' => static::version(),
                'csrfToken' => csrf_token(),
                'siteRoot' => site_root(),
                'cpRoot' => cp_root(),
                'urlPath' => '/' . request()->path(),
                'resourceUrl' => cp_resource_url('/'),
                'locales' => \Statamic\API\Config::get('statamic.system.locales'),
                'markdownHardWrap' => \Statamic\API\Config::get('statamic.theming.markdown_hard_wrap'),
                'conditions' => [],
                'MediumEditorExtensions' => [],
                'flash' => []
            ];
        }

        static::$jsonVariables = array_merge(static::$jsonVariables, $variables);

        return new static;
    }
}

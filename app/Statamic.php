<?php

namespace Statamic;

use Closure;
use Statamic\Facades\URL;
use Statamic\Facades\File;
use Statamic\Facades\Site;
use Stringy\StaticStringy;
use Illuminate\Http\Request;

class Statamic
{
    const CORE_SLUG = 'statamic';
    const CORE_REPO = 'statamic/definitely-not-v3'; // TODO: Change to `statamic/cms`

    protected static $scripts = [];
    protected static $styles = [];
    protected static $cpRoutes = [];
    protected static $webRoutes = [];
    protected static $actionRoutes = [];
    protected static $jsonVariables = [];

    public static function version()
    {
        return \Facades\Statamic\Version::get();
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
        $defaults = [
            'version' => static::version(),
            'laravelVersion' => app()->version(),
            'csrfToken' => csrf_token(),
            'cpRoot' => cp_root(),
            'urlPath' => '/' . request()->path(),
            'resourceUrl' => Statamic::assetUrl(),
            'locales' => \Statamic\Facades\Config::get('statamic.system.locales'),
            'markdownHardWrap' => \Statamic\Facades\Config::get('statamic.theming.markdown_hard_wrap'),
            'conditions' => [],
            'MediumEditorExtensions' => [],
            'flash' => static::flash(),
            'ajaxTimeout' => config('statamic.system.ajax_timeout'),
            'googleDocsViewer' => config('statamic.assets.google_docs_viewer'),
            'user' => ($user = user()) ? $user->toJavascriptArray() : [],
            'paginationSize' => config('statamic.cp.pagination_size'),
        ];

        $vars = array_merge($defaults, static::$jsonVariables);

        return collect($vars)->map(function ($variable) use ($request) {
            return is_callable($variable) ? $variable($request) : $variable;
        })->all();
    }

    public static function provideToScript(array $variables)
    {
        static::$jsonVariables = array_merge(static::$jsonVariables, $variables);

        return new static;
    }

    public static function svg($name)
    {
        return StaticStringy::collapseWhitespace(
            File::get(statamic_path("resources/dist/svg/{$name}.svg"))
        );
    }

    public static function assetUrl($url = '/')
    {
        return static::url('vendor/statamic/cp/' . $url);
    }

    public static function url($url = '/')
    {
        return URL::tidy(Site::default()->url() . '/' . $url);
    }

    public static function flash()
    {
        if ($success = session('success')) {
            $messages[] = ['type' => 'success', 'message' => $success];
        }

        return $messages ?? [];
    }
}

<?php

use Statamic\Facades\URL;
use Statamic\Facades\Str;
use Statamic\Facades\Path;
use Statamic\Facades\Site;
use Statamic\Facades\Config;
use Michelf\SmartyPants;
use Statamic\Extend\Addon;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Stringy\StaticStringy as Stringy;

if (! function_exists('array_get_colon')) {
    /**
     * Get an item from an array using "dot" or "colon" notation.
     *
     * @param  array  $array
     * @param  string $key
     * @param  mixed  $default
     * @return mixed
     */
    function array_get_colon($array, $key, $default = null)
    {
        if ($key) {
            $key = str_replace(':', '.', $key);
        }

        return Arr::get($array, $key, $default);
    }
}

if (! function_exists('array_has_colon')) {
    /**
     * Check if an item from an array exists using "dot" or "colon" notation.
     *
     * @param  array  $array
     * @param  string $key
     * @return mixed
     */
    function array_has_colon($array, $key)
    {
        if ($key) {
            $key = str_replace(':', '.', $key);
        }

        return Arr::has($array, $key);
    }
}


/**
 * Gets the site's default locale
 *
 * @return string
 */
function default_locale()
{
    return Config::getDefaultLocale();
}

function path($from, $extra = null)
{
    return Path::tidy($from . '/' . $extra);
}

function cache_path($path = null)
{
    return path(storage_path('statamic/cache'), $path);
}

function addons_path($path = null)
{
    return path(base_path('addons'), $path);
}

function temp_path($path = null)
{
    return path(storage_path('statamic/temp'), $path);
}

function site_handle($handle = null)
{
    return site_locale($handle);
}

/**
 * Gets or sets the site locale
 *
 * @param string|null $locale
 * @return string
 */
function site_locale($locale = null)
{
    if ($locale) {
        return Site::setCurrent($locale);
    }

    return Site::current()->handle();
}

/**
 * @param array $value
 * @return \Statamic\Data\Content\ContentCollection
 */
function collect_content($value = [])
{
    return new \Statamic\Data\Content\ContentCollection($value);
}

/**
 * Gets an addon's API class if it exists, or creates a temporary generic addon class.
 *
 * @param string $addon
 * @return Addon|API
 */
function addon($addon)
{
    try {
        $addon = app("Statamic\\Addons\\{$addon}\\{$addon}API");
    } catch (ReflectionException $e) {
        $addon = new Addon($addon);
    }

    return $addon;
}


/**
 * Turns a string into a slug
 *
 * @param string $var
 * @return string
 */
function slugify($value)
{
    return Stringy::slugify($value);
}

/**
 * Make sure a URL /looks/like/this
 *
 * @param string $url Any given URL
 * @return string
 */
function format_url($url)
{
    return '/' . trim($url, '/');
}

function cp_route($route, $params = [])
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

/**
 * Parse string with basic Markdown
 *
 * @param $content
 * @return mixed
 */
function markdown($content)
{
    $markdown = new \ParsedownExtra();

    return $markdown->text($content);
}

/**
 * Run through Smartypants
 *
 * @param $content
 * @return mixed
 */
function smartypants($content)
{
    return SmartyPants::defaultTransform($content);
}

/**
 * Sanitizes a string
 *
 * @param bool $antlers  Whether Antlers (curly braces) should be escaped.
 * @return string
 */

function sanitize($value, $antlers = true)
{
    if (is_array($value)) {
        return sanitize_array($value, $antlers);
    }

    $value = htmlentities($value);

    if ($antlers) {
        $value = str_replace(['{', '}'], ['&lbrace;', '&rbrace;'], $value);
    }

    return $value;
}

/**
 * Recusive friendly method of sanitizing an array.
 *
 * @param bool $antlers  Whether Antlers (curly braces) should be escaped.
 * @return array
 */
function sanitize_array($array, $antlers = true)
{
    $result = array();

    foreach ($array as $key => $value) {
        $key = htmlentities($key);
        $result[$key] = sanitize($value, $antlers);
    }

    return $result;
}

function translate($id, array $parameters = [])
{
    return trans($id, $parameters);
}

function translate_choice($id, $number, array $parameters = [])
{
    return trans_choice($id, $number, $parameters);
}


if (! function_exists('array_filter_use_both')) {
    /**
     * Polyfill for the array_filter constant ARRAY_FILTER_USE_BOTH.
     *
     * This filters the array passing the key as the second parameter
     * for more complex filtering.
     *
     * BC for `array_filter($array, $callback, ARRAY_FILTER_USE_BOTH);`
     *
     * @param  array  $array
     * @param  Closure  $callback
     * @return array
     */
    function array_filter_use_both($array, $callback)
    {
        $items = [];

        foreach ($array as $key => $value) {
            if (! $callback($value, $key)) {
                continue;
            }

            $items[$key] = $value;
        }

        return $items;
    }
}

/**
 * Returns a real boolean from a string based boolean
 *
 * @param string $value
 * @return bool
 */
function bool($value)
{
    return ! in_array(strtolower($value), ['no', 'false', '0', '', '-1']);
}


/**
 * Filtering a array by its keys using a callback.
 *
 * @param $array array The array to filter
 * @param $callback Callback The filter callback, that will get the key as first argument.
 *
 * @return array The remaining key => value combinations from $array.
 */
function array_filter_key(array $array, $callback)
{
    $matchedKeys = array_filter(array_keys($array), $callback);

    return array_intersect_key($array, array_flip($matchedKeys));
}

/**
 * Return a real integer from a string based integer
 *
 * @param string $value
 * @return int
 */
function int($value)
{
    return intval($value);
}
/**
 * @param array $value
 * @return \Statamic\Taxonomies\TermCollection
 */
function collect_terms($value = [])
{
    return new \Statamic\Taxonomies\TermCollection($value);
}


function carbon($value)
{
    if (! $value instanceof Carbon) {
        $value = (is_numeric($value)) ? Carbon::createFromTimestamp($value) : Carbon::parse($value);
    }

    return $value;
}
/**
 * Reindex an array so unnamed keys are named
 *
 * @param array $array
 * @return mixed
 */
function array_reindex($array)
{
    if (array_values($array) === $array) {
        $array = array_flip($array);
    }

    return $array;
}

function root_path()
{
    return base_path();
}

function bundles_path($path = null)
{
    return path(statamic_path('bundles'), $path);
}


/**
 * Parse string with basic Textile
 *
 * @param $content
 * @return string
 */
function textile($content)
{
    $parser = new \Netcarver\Textile\Parser();

    return $parser
        ->setDocumentType('html5')
        ->parse($content);
}

/**
 * @param array $value
 * @return \Statamic\Data\Pages\PageCollection
 */
function collect_pages($value = [])
{
    return new \Statamic\Data\Pages\PageCollection($value);
}

function bool_str($bool)
{
    return ((bool) $bool) ? 'true' : 'false';
}

function cp_root()
{
    return str_start(config('statamic.cp.route'), '/');
}

/**
 * Inline SVG helper
 *
 * Outputs the contents of an svg file
 *
 * @param string $src Name of svg
 * @return string
 */
function inline_svg($src)
{
    return Stringy::collapseWhitespace(
        File::get(statamic_path("resources/dist/svg/{$src}.svg"))
    );
}

function statamic_path($path = null)
{
    return path(__DIR__ . '/../', $path);
}

/**
 * Shorthand for translate()
 *
 * @param string $var
 * @param array  $params
 * @return string
 */
function t($var, $params = [])
{
    return translate('cp.'.$var, $params);
}

/**
 * Return a gravatar image
 *
 * @param  string  $email
 * @param  integer $size
 * @return string
 */
function gravatar($email, $size = null)
{
    $url = "https://www.gravatar.com/avatar/" . e(md5(strtolower($email)));

    if ($size) {
        $url .= '?s=' . $size;
    }

    return $url;
}

/**
 * @param array $value
 * @return \Statamic\Auth\UserCollection
 */
function collect_users($value = [])
{
    return new \Statamic\Auth\UserCollection($value);
}

/**
 * Check whether the nav link is active
 *
 * @param string $url
 * @return bool
 */
function nav_is($url)
{
    return is_current($url);
}

/**
 * Returns true if CP URL pattern matches current URL
 *
 * @param string $pattern
 * @return bool
 */
function is_current($pattern)
{
    return request()->is(config('statamic.cp.route') . '/' . $pattern);
}

function current_class($pattern)
{
    return is_current($pattern) ? 'current' : '';
}

/**
 * @param array $value
 * @return \Statamic\Assets\AssetCollection
 */
function collect_assets($value = [])
{
    return new \Statamic\Assets\AssetCollection($value);
}

if (! function_exists('start_measure')) {
    function start_measure()
    {
        // Prevent errors if debug bar is not installed.
    }
}

if (! function_exists('stop_measure')) {
    function stop_measure()
    {
        // Prevent errors if debug bar is not installed.
    }
}

if (!function_exists('mb_str_word_count')) {
    /**
     * Multibyte version of str_word_count
     *
     * @param string $string
     * @param int $format
     * @param string $charlist
     *
     * @link https://stackoverflow.com/a/17725577/1569621
     */
    function mb_str_word_count($string, $format = 0, $charlist = '[]')
    {
        $words = empty($string = trim($string)) ? [] : preg_split('~[^\p{L}\p{N}\']+~u', $string);

        switch ($format) {
            case 0:
                return count($words);
                break;
            case 1:
            case 2:
                return $words;
                break;
            default:
                return $words;
                break;
        }
    };
}

if (! function_exists('back_or_route')) {
    /**
     * Redirect back to the previous page, or if there is no referer redirect to a route.
     */
    function back_or_route($route)
    {
        $referrer = request()->header('referer');

        if (! $referrer || $referrer === request()->getUri()) {
            return redirect()->route($route);
        }

        return back();
    }
}

if (! function_exists('__n')) {
    function __n($key, $number, $replace = [], $locale = null)
    {
        return trans_choice(__($key, $replace, $locale), $number);
    }
}

if (! function_exists('__s')) {
    function __s($key, $replace = [], $locale = null)
    {
        return trans('statamic::messages.'.$key, $replace, $locale);
    }
}

if (! function_exists('user')) {
    function user()
    {
        return \Statamic\Facades\User::current();
    }
}

if (! function_exists('me')) {
    function me()
    {
        return user();
    }
}

if (! function_exists('my')) {
    function my()
    {
        return user();
    }
}

if (! function_exists('debugbar')) {
    function debugbar()
    {
        return optional();
    }
}

if (! function_exists('crumb')) {
    function crumb(...$values)
    {
        return implode(' ‹ ', array_map("__", $values));
    }
}

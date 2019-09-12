<?php

use Statamic\Facades\URL;
use Statamic\Facades\Str;
use Statamic\Facades\Path;
use Statamic\Facades\Site;
use Statamic\Facades\Config;
use Statamic\Extend\Addon;
use Illuminate\Support\Carbon;
use Stringy\StaticStringy as Stringy;

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
    return Path::tidy(__DIR__ . '/../' . $path);
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
    return trans('cp.'.$var, $params);
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

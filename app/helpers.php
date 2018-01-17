<?php

use Statamic\API\URL;
use Statamic\API\Str;
use Statamic\API\Path;
use Statamic\Extend\Addon;
use Michelf\MarkdownExtra;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Stringy\StaticStringy as Stringy;

define('STATAMIC_VERSION', '3.0.0');

$GLOBALS['statamictodos'] = [];
function log_todo()
{
    $backtrace = debug_backtrace()[1];
    $str = array_get($backtrace, 'class', '') . '::' . $backtrace['function'];

    if (!array_has($GLOBALS['statamictodos'], $str)) {
        \Log::debug('Todo: ' . $str);
        $GLOBALS['statamictodos'][$str] = true;
    }
}


if (! function_exists('array_get')) {
    /**
     * Get an item from an array using "dot" or "colon" notation.
     *
     * @param  array  $array
     * @param  string $key
     * @param  mixed  $default
     * @return mixed
     */
    function array_get($array, $key, $default = null)
    {
        if ($key) {
            $key = str_replace(':', '.', $key);
        }

        return Arr::get($array, $key, $default);
    }
}


/**
 * Gets the site's default locale
 *
 * @return string
 */
function default_locale()
{
    log_todo();
    return 'en';
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

/**
 * Gets or sets the site locale
 *
 * @param string|null $locale
 * @return string
 */
function site_locale($locale = null)
{
    if ($locale) {
        return config(['app.locale' => $locale]);
    }

    return config('app.locale');
}

/**
 * @param array $value
 * @return \Statamic\FileCollection;
 */
function collect_files($value = [])
{
    return new \Statamic\FileCollection($value);
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
    if (! config('cp.enabled')) {
        return null;
    }

    return route($route, $params);
}

/**
 * Parse string with basic Markdown
 *
 * @param $content
 * @return mixed
 */
function markdown($content)
{
    $parser = new MarkdownExtra;

    if (Config::get('theming.markdown_hard_wrap')) {
        $parser->hard_wrap = true;
    }

    return $parser->transform($content);
}

/**
 * @return \Statamic\DataStore
 */
function datastore()
{
    return app('Statamic\DataStore');
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
        $result[$key] = sanitize($value);
    }

    return $result;
}

/**
 * @param array $value
 * @return \Statamic\Data\Globals\GlobalCollection
 */
function collect_globals($value = [])
{
    return new \Statamic\Data\Globals\GlobalCollection($value);
}

function translate($id, array $parameters = [])
{
    return trans($id, $parameters);
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
 * @param array $value
 * @return \Statamic\Data\Entries\EntryCollection
 */
function collect_entries($value = [])
{
    return new \Statamic\Data\Entries\EntryCollection($value);
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
 * @return \Statamic\Data\Taxonomies\TermCollection
 */
function collect_terms($value = [])
{
    return new \Statamic\Data\Taxonomies\TermCollection($value);
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

function cp_resource_url($url)
{
    return resource_url('cp/' . $url);
}

function resource_url($url)
{
    log_todo();
    return '/resources/' . $url;
}

function site_root()
{
    log_todo();
    return '/';
}

function resources_root()
{
    log_todo();
    return '_resources';
}

function cp_root()
{
    log_todo();
    return '/cp';
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
 * SVG helper
 *
 * Outputs a tag to reference a symbol in the sprite.
 *
 * @param string $name Name of svg
 * @return string
 */
function svg($name)
{
    return '<svg><use xlink:href="#'.$name.'" /></svg>';
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
 * @return \Statamic\Data\Users\UserCollection
 */
function collect_users($value = [])
{
    return new \Statamic\Data\Users\UserCollection($value);
}

/**
 * Check whether the nav link is active
 *
 * @param string $url
 * @return string
 */
function nav_is($url)
{
    $url = preg_replace('/^index\.php\//', '', $url);
    $current = request()->url();

    return $url === $current || Str::startsWith($current, $url . '/');
}

function format_input_options($options)
{
    $formatted_options = [];

    foreach ($options as $key => $text) {
        if ($options === array_values($options)) {
            $formatted_options[] = ['value' => $text, 'text' => $text];
        } else {
            $formatted_options[] = ['value' => $key, 'text' => $text];
        }
    }

    return $formatted_options;
}


/**
 * @param array $value
 * @return \Statamic\Assets\AssetCollection
 */
function collect_assets($value = [])
{
    return new \Statamic\Assets\AssetCollection($value);
}

function format_update($string)
{
    $string = markdown($string);
    $string = Str::replace($string, '[new]', '<span class="label label-info">New</span>');
    $string = Str::replace($string, '[fix]', '<span class="label label-success">Fix</span>');
    $string = Str::replace($string, '[break]', '<span class="label label-danger">Break</span>');

    return $string;
}

function start_measure()
{
    // make things work until debug bar is back
}

function stop_measure()
{
    // make things work until debug bar is back
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

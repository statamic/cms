<?php

use Statamic\Facades\Path;
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

if (! function_exists('user')) {
    function user()
    {
        return \Statamic\Facades\User::current();
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

<?php

use Stringy\Stringy;
use Statamic\API\Path;

define('STATAMIC_VERSION', '3.0.0');

function log_todo()
{
    $backtrace = debug_backtrace()[1];
    Log::debug(sprintf('Todo: %s::%s', array_get($backtrace, 'class', ''), $backtrace['function']));
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
 * Turns a string into a slug
 *
 * @param string $var
 * @return string
 */
function slugify($value)
{
    return Stringy::slugify($value);
}

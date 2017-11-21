<?php

use Statamic\API\Path;

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

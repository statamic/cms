<?php

namespace Statamic\StaticCaching;

interface UrlExcluder
{
    /**
     * Determine whether URL should be excluded from caching.
     *
     * @param  string  $url  Url.
     * @return bool
     */
    public function isExcluded(string $url): bool;
}

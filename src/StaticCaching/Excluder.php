<?php

namespace Statamic\StaticCaching;

interface Excluder
{
    /**
     * Determine whether URL should be excluded from caching.
     *
     * @param  string  $url  Url.
     * @return bool
     */
    public function __invoke(string $url): bool;
}

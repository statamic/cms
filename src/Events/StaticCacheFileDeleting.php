<?php

namespace Statamic\Events;

/**
 * Dispatched before a full measure cache file is deleted.
 *
 * Listeners can read the existing file and clean up any related sidecar files.
 */
class StaticCacheFileDeleting extends Event
{
    public function __construct(public string $path, public ?string $url, public ?string $site)
    {
    }
}

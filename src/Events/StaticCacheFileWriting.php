<?php

namespace Statamic\Events;

/**
 * Dispatched before a full measure cache file is written.
 *
 * The file contains the previous page, or doesn't exist yet. The write may still
 * be skipped if another process holds the file lock.
 */
class StaticCacheFileWriting extends Event
{
    public function __construct(public string $path, public ?string $url, public ?string $site)
    {
    }
}

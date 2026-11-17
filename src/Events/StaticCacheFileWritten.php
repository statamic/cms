<?php

namespace Statamic\Events;

/**
 * Dispatched after the full measure static cache has written a page to disk.
 *
 * The file handle is closed and its lock released, so listeners may read the file,
 * modify it, or write sidecar files next to it. Not dispatched when the write was
 * skipped because another process held the lock.
 */
class StaticCacheFileWritten extends Event
{
    public function __construct(public string $path, public ?string $url, public ?string $site)
    {
    }
}

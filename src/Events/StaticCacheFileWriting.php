<?php

namespace Statamic\Events;

/**
 * Dispatched right before the full measure static cache writes a page to disk.
 *
 * The file still contains the previous version of the page, or doesn't exist yet.
 * Listeners that need to read it must run synchronously. Also dispatched when the
 * write ends up being skipped because another process holds the lock, in which
 * case no StaticCacheFileWritten event follows.
 *
 * Returning false does not prevent the write, but an exception thrown by a
 * synchronous listener does.
 */
class StaticCacheFileWriting extends Event
{
    public function __construct(public string $path, public ?string $url, public ?string $site)
    {
    }
}

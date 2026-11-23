<?php

namespace Statamic\Events;

/**
 * Dispatched after a full measure cache file is written.
 *
 * The file is closed and unlocked, ready for listeners to read or modify.
 * Skipped writes do not dispatch this event.
 */
class StaticCacheFileWritten extends Event
{
    public function __construct(public string $path, public ?string $url, public ?string $site)
    {
    }
}

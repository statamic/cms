<?php

namespace Statamic\Tokens\Handlers;

use Facades\Statamic\CP\LivePreview;
use Statamic\Facades\Entry;

class LivePreviewEntry
{
    public function handle($token)
    {
        $entry = LivePreview::item($token);

        Entry::substitute($entry);
    }
}

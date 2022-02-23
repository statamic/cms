<?php

namespace Statamic\Tokens\Handlers;

use Closure;
use Facades\Statamic\CP\LivePreview;
use Facades\Statamic\View\Cascade;
use Statamic\Contracts\Tokens\Token;
use Statamic\Facades\Entry;

class LivePreviewEntry
{
    public function handle(Token $token, $request, Closure $next)
    {
        $entry = LivePreview::item($token);

        $entry->setSupplement('is_live_preview', true);
        Cascade::hydrated(fn ($cascade) => $cascade->set('is_live_preview', true));

        Entry::substitute($entry);

        $response = $next($request);

        $response->headers->set('X-Statamic-Live-Preview', true);

        return $response;
    }
}

<?php

namespace Statamic\Tokens\Handlers;

use Closure;
use Facades\Statamic\CP\LivePreview;
use Statamic\Contracts\Tokens\Token;
use Statamic\Facades\Entry;

class LivePreviewEntry
{
    public function handle(Token $token, $request, Closure $next)
    {
        $entry = LivePreview::item($token);

        Entry::substitute($entry);

        $response = $next($request);

        $response->headers->set('X-Statamic-Live-Preview', true);

        return $response;
    }
}

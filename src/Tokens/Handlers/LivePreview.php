<?php

namespace Statamic\Tokens\Handlers;

use Closure;
use Facades\Statamic\CP\LivePreview as Facade;
use Statamic\Contracts\Tokens\Token;
use Statamic\Facades\Site;

class LivePreview
{
    public function handle(Token $token, $request, Closure $next)
    {
        $item = Facade::item($token);

        $item->repository()->substitute($item);

        $response = $next($request);

        if (Site::multiEnabled()) {
            $validURLs = Site::all()
                ->map(function ($site) {
                    $parts = parse_url($site->absoluteUrl());

                    return $parts['scheme'].'://'.$parts['host'];
                })->values()
                ->unique()
                ->join(' ');
            $response->headers->set('Content-Security-Policy "frame-ancestors '.$validURLs.'"');
        }

        $response->headers->set('X-Statamic-Live-Preview', true);

        return $response;
    }
}

<?php

namespace Statamic\Tokens\Handlers;

use Closure;
use Facades\Statamic\CP\LivePreview as Facade;
use Illuminate\Support\Collection;
use Statamic\Contracts\Tokens\Token;
use Statamic\Facades\Site as Sites;
use Statamic\Sites\Site;

class LivePreview
{
    public function handle(Token $token, $request, Closure $next)
    {
        $item = Facade::item($token);

        $item->repository()->substitute($item);

        $response = $next($request);

        if (Sites::multiEnabled()) {
            /** @var Collection */
            $siteURLs = Sites::all()
                ->map(fn (Site $site) => $this->getSchemeAndHost($site))
                ->values()
                ->unique()
                ->join(' ');

            $response->headers->set('Content-Security-Policy', "frame-ancestors $siteURLs");
        }

        $response->headers->set('X-Statamic-Live-Preview', true);

        return $response;
    }

    private function getSchemeAndHost(Site $site): string
    {
        $parts = parse_url($site->absoluteUrl());

        return $parts['scheme'].'://'.$parts['host'];
    }
}

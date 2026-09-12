<?php

namespace Statamic\Tokens\Handlers;

use Closure;
use Facades\Statamic\CP\LivePreview as Facade;
use Illuminate\Support\Collection;
use Statamic\Contracts\Tokens\Token;
use Statamic\Facades\Site as Sites;
use Statamic\Sites\Site;
use Statamic\View\Instrumentation\FieldDomTracer;
use Statamic\View\Instrumentation\FieldMarkers;
use Statamic\View\Instrumentation\InstrumentationManager;

class LivePreview
{
    public function handle(Token $token, $request, Closure $next)
    {
        $item = Facade::item($token);

        if (! $item) {
            return $next($request);
        }

        $item->repository()->substitute($item);

        $manager = app(InstrumentationManager::class);
        $tracer = new FieldDomTracer;
        $markers = FieldMarkers::make()->using($tracer);

        $manager->register($markers);

        try {
            $response = $tracer->capture(fn () => $next($request));
        } finally {
            $manager->forget($markers);
        }

        if ($response->isSuccessful()
            && ! $request->isMethod('HEAD')
            && str_contains($response->headers->get('Content-Type', ''), 'text/html')
            && is_string($response->getContent())) {
            $this->addFieldManifest($response, $item, $tracer);
        }

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

    private function addFieldManifest($response, $item, FieldDomTracer $tracer): void
    {
        $manifest = json_encode([
            'version' => 1,
            'owner' => ['reference' => $item->reference(), 'locale' => $item->locale()],
            'fields' => $tracer->fields(),
            'markers' => $tracer->markers(),
        ], JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_INVALID_UTF8_SUBSTITUTE);

        $html = $response->getContent();
        $data = '<script type="application/json" id="statamic-preview-fields">'.$manifest.'</script>';
        $position = strripos($html, '</body>');

        if ($position === false) {
            $html .= $data;
        } else {
            $html = substr_replace($html, $data, $position, 0);
        }

        $response->setContent($html);
        $response->headers->remove('Content-Length');
        $response->headers->remove('ETag');
    }

    private function getSchemeAndHost(Site $site): string
    {
        $parts = parse_url($site->absoluteUrl());

        $port = isset($parts['port']) ? ':'.$parts['port'] : '';

        return $parts['scheme'].'://'.$parts['host'].$port;
    }
}

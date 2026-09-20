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

        $tracer = new FieldDomTracer;

        $response = $this->traceFields($tracer, fn () => $next($request));

        if ($this->shouldAddFieldManifest($request, $response)) {
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

    private function traceFields(FieldDomTracer $tracer, Closure $callback)
    {
        $manager = app(InstrumentationManager::class);
        $markers = FieldMarkers::make()->using($tracer);

        $manager->register($markers);

        try {
            return $tracer->capture($callback);
        } finally {
            $manager->forget($markers);
        }
    }

    private function shouldAddFieldManifest($request, $response): bool
    {
        return $response->isSuccessful()
            && ! $request->isMethod('HEAD')
            && str_contains($response->headers->get('Content-Type', ''), 'text/html')
            && is_string($response->getContent());
    }

    private function addFieldManifest($response, $item, FieldDomTracer $tracer): void
    {
        $script = '<script type="application/json" id="statamic-preview-fields">'.$this->fieldManifest($item, $tracer).'</script>';

        $response->setContent($this->injectBeforeBodyClose($response->getContent(), $script));
        $response->headers->remove('Content-Length');
        $response->headers->remove('ETag');
    }

    private function fieldManifest($item, FieldDomTracer $tracer): string
    {
        return json_encode([
            'version' => 1,
            'owner' => ['reference' => $item->reference(), 'locale' => $item->locale()],
            'fields' => $tracer->fields(),
            'markers' => $tracer->markers(),
        ], JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_INVALID_UTF8_SUBSTITUTE);
    }

    private function injectBeforeBodyClose(string $html, string $script): string
    {
        $position = strripos($html, '</body>');

        return $position === false
            ? $html.$script
            : substr_replace($html, $script, $position, 0);
    }

    private function getSchemeAndHost(Site $site): string
    {
        $parts = parse_url($site->absoluteUrl());

        $port = isset($parts['port']) ? ':'.$parts['port'] : '';

        return $parts['scheme'].'://'.$parts['host'].$port;
    }
}

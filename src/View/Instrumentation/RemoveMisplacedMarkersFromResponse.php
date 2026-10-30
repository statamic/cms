<?php

namespace Statamic\View\Instrumentation;

use Illuminate\Foundation\Http\Events\RequestHandled;
use Illuminate\Http\Response;
use InvalidArgumentException;

/** @internal */
class RemoveMisplacedMarkersFromResponse
{
    public function __construct(protected InstrumentationManager $manager)
    {
    }

    public function handle(RequestHandled $event): void
    {
        try {
            if (! $this->manager->instrumenting()) {
                return;
            }
        } catch (InvalidArgumentException) {
            return;
        }

        $response = $event->response;
        $content = $response->getContent();

        if (! is_string($content) || $content === '') {
            return;
        }

        if (! str_contains(strtolower((string) $response->headers->get('Content-Type', 'text/html')), 'html')) {
            return;
        }

        $cleaned = $this->manager->removeMisplacedMarkers($content);

        if ($cleaned === $content) {
            return;
        }

        $original = $response instanceof Response ? $response->original : null;

        $response->setContent($cleaned);

        if ($original !== null && ! is_string($original)) {
            $response->original = $original;
        }

        if ($response->headers->has('Content-Length')) {
            $response->headers->set('Content-Length', (string) strlen($cleaned));
        }

        if ($response->getEtag() === '"'.md5($content).'"') {
            $response->setEtag(md5($cleaned));
        }
    }
}

<?php

namespace Statamic\Http\Middleware\CP;

use Closure;
use Illuminate\Http\Request;
use Statamic\CP\Toasts\Manager;
use Statamic\CP\Toasts\Toast;
use Symfony\Component\HttpFoundation\Response;

class AddToasts
{
    /**
     * @var Manager
     */
    protected $toasts;

    public function __construct(Manager $toasts)
    {
        $this->toasts = $toasts;
    }

    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);

        if ($this->hasNoToasts()) {
            return $response;
        } else {
            return $this->addToastsToResponseIfPossible($response);
        }
    }

    private function addToastsToResponseIfPossible(Response $response): Response
    {
        $content = $this->unwrapAndParseContentAsArray($response);

        if (is_null($content)) {
            return $response;
        }

        if ($this->isRedirect($content)) {
            return $response;
        } else {
            $contentWithToasts = $this->addToastsToContent($content);
            $this->toasts->clear();

            return $this->setContentFor($response, $contentWithToasts);
        }
    }

    private function isRedirect(array $content): bool
    {
        return array_has($content, 'redirect');
    }

    private function addToastsToContent(array $content): array
    {
        $toasts = $this->getToastsAsArray();

        $content['_toasts'] = $toasts;

        return $content;
    }

    private function unwrapAndParseContentAsArray(Response $response): ?array
    {
        $content = $response->getContent();

        if ($content === false) {
            return null;
        }

        $parsedContent = json_decode($content, true);

        if ($parsedContent === null) {
            return null;
        }

        if (! is_array($parsedContent)) {
            return null;
        }

        return $parsedContent;
    }

    private function setContentFor(Response $response, array $newContent): Response
    {
        $json = json_encode($newContent);

        return $response->setContent($json);
    }

    private function getToastsAsArray(): array
    {
        $toasts = $this->toasts->all();

        return array_map(function (Toast $toast) {
            return $toast->toArray();
        }, $toasts);
    }

    private function hasNoToasts(): bool
    {
        $toasts = $this->toasts->all();

        return empty($toasts);
    }
}

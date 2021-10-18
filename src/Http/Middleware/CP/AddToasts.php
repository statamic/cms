<?php

namespace Statamic\Http\Middleware\CP;

use Closure;
use Illuminate\Http\Request;
use Statamic\CP\Toasts\Toast;
use Statamic\CP\Toasts\ToastsHolder;
use Symfony\Component\HttpFoundation\Response;

class AddToasts
{
    /**
     * @var ToastsHolder
     */
    protected $toastsHolder;

    public function __construct(ToastsHolder $toastsHolder)
    {
        $this->toastsHolder = $toastsHolder;
    }

    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);

        if ($this->isRedirect($response)) {
            return $response;
        } else {
            return $this->addToastsTo($response);
        }
    }

    private function isRedirect(Response $response): bool
    {
        $parsedContent = $this->unwrapAndParseContentAsArray($response);

        if ($parsedContent == null) {
            return false;
        } else {
            return array_has($parsedContent, 'redirect');
        }
    }

    private function addToastsTo(Response $response): Response
    {
        $toasts = $this->getToastsAsArray();

        if (empty($toasts)) {
            return $response;
        }

        $parsedContent = $this->unwrapAndParseContentAsArray($response);

        if ($parsedContent == null) {
            return $response;
        }

        $parsedContent['_toasts'] = $toasts;
        $newContent = json_encode($parsedContent);

        return $response->setContent($newContent);
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

    private function getToastsAsArray(): array
    {
        $toasts = $this->toastsHolder->all();

        return array_map(function (Toast $toast) {
            return $toast->toArray();
        }, $toasts);
    }
}

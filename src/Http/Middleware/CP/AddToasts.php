<?php

namespace Statamic\Http\Middleware\CP;

use Closure;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Statamic\CP\Toasts\Manager;
use Statamic\Support\Arr;

class AddToasts
{
    protected $toasts;

    public function __construct(Manager $toasts)
    {
        $this->toasts = $toasts;
    }

    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);

        $toasts = $this->toasts->toArray();

        if (empty($toasts) || ! $response instanceof JsonResponse) {
            return $response;
        }

        $content = $response->getData(true);

        if (Arr::has($content, 'redirect')) {
            return $response;
        }

        $content['_toasts'] = $toasts;

        $this->toasts->clear();

        return $response->setData($content);
    }
}

<?php

namespace Statamic\Tokens\Handlers;

use Closure;
use Statamic\Contracts\Tokens\Token;
use Statamic\Facades\Data;

class SharedPreview
{
    public function handle(Token $token, $request, Closure $next)
    {
        $item = Data::find($token->get('reference'));

        if (! $item) {
            return $next($request);
        }

        if (
            method_exists($item, 'hasWorkingCopy')
            && $item->revisionsEnabled()
            && $item->hasWorkingCopy()
        ) {
            $item = $item->fromWorkingCopy();
        }

        if (method_exists($item, 'repository')) {
            $item->repository()->substitute($item);
        }

        $response = $next($request);

        $response->headers->set('X-Statamic-Shared-Preview', true);

        return $response;
    }
}

<?php

namespace Statamic\Tokens\Handlers;

use Closure;
use Facades\Statamic\CP\SharedPreview as Facade;
use Statamic\Contracts\Tokens\Token;
use Statamic\Exceptions\NotFoundHttpException;

class SharedPreview
{
    public function handle(Token $token, $request, Closure $next)
    {
        $item = Facade::item($token);

        if ($item === false) {
            throw new NotFoundHttpException;
        }

        if (! $item) {
            return $next($request);
        }

        if (method_exists($item, 'setSupplement')) {
            $item->setSupplement('shared_preview', [
                'expires_at' => $token->expiry()->toIso8601String(),
                'revision' => $token->get('revision'),
            ]);
        }

        if (method_exists($item, 'repository')) {
            $item->repository()->substitute($item);
        }

        $response = $next($request);

        $response->headers->set('X-Statamic-Shared-Preview', true);
        $response->headers->set('X-Robots-Tag', 'noindex');

        return $response;
    }
}

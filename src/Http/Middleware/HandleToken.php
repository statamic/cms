<?php

namespace Statamic\Http\Middleware;

use Closure;
use Statamic\Facades\Token;

class HandleToken
{
    public function handle($request, Closure $next)
    {
        if ($token = $this->getToken($request)) {
            $token->handle();
        }

        return $next($request);
    }

    private function getToken($request)
    {
        if ($token = $request->token ?? $request->header('X-Statamic-Token')) {
            return Token::find($token);
        }
    }
}

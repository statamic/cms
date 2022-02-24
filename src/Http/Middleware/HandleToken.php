<?php

namespace Statamic\Http\Middleware;

use Closure;
use Statamic\Facades\Token;

class HandleToken
{
    public function handle($request, Closure $next)
    {
        if (! $token = $this->getToken($request)) {
            return $next($request);
        }

        $this->collectGarbage();

        return $token->handle($request, $next);
    }

    private function getToken($request)
    {
        if ($token = $request->token ?? $request->header('X-Statamic-Token')) {
            return Token::find($token);
        }
    }

    private function collectGarbage()
    {
        $lottery = [2, 100];

        if (random_int(1, $lottery[1]) <= $lottery[0]) {
            Token::collectGarbage();
        }
    }
}

<?php

namespace Statamic\Http\Middleware;

use Closure;
use Statamic\Facades\Token;

class HandleToken
{
    public function handle($request, Closure $next)
    {
        if (! $token = $request->statamicToken()) {
            return $next($request);
        }

        $this->collectGarbage();

        return $token->handle($request, $next);
    }

    private function collectGarbage()
    {
        $lottery = [2, 100];

        if (random_int(1, $lottery[1]) <= $lottery[0]) {
            Token::collectGarbage();
        }
    }
}

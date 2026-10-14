<?php

namespace Statamic\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Statamic\Exceptions\NotFoundHttpException;
use Symfony\Component\HttpFoundation\Response;

class EnsureFrontendAuthEnabled
{
    public function handle(Request $request, Closure $next): Response
    {
        throw_unless(
            config('statamic.users.frontend_auth_enabled', true),
            new NotFoundHttpException
        );

        return $next($request);
    }
}

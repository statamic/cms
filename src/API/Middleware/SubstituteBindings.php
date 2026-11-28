<?php

namespace Statamic\API\Middleware;

use Closure;
use Illuminate\Routing\Middleware\SubstituteBindings as LaravelSubstituteBindings;

// Wraps rather than extends Laravel's middleware, since the API routes exclude
// it (and its subclasses) so that bindings aren't resolved on cache hits.
class SubstituteBindings
{
    public function handle($request, Closure $next)
    {
        return app(LaravelSubstituteBindings::class)->handle($request, $next);
    }
}

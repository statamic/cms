<?php

namespace Statamic\Http\Middleware\CP;

use Closure;
use Statamic\Facades\Permission;

class BootPermissions
{
    public function handle($request, Closure $next)
    {
        Permission::boot();

        return $next($request);
    }
}

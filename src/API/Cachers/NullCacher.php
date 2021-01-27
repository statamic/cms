<?php

namespace Statamic\API\Cachers;

use Closure;
use Illuminate\Http\Request;
use Statamic\API\Cacher;

class NullCacher implements Cacher
{
    public function remember(Request $request, Closure $callback)
    {
        return $callback();
    }
}

<?php

namespace Statamic\API;

use Closure;
use Illuminate\Http\Request;

interface Cacher
{
    /**
     * Remember an API request.
     *
     * @param \Illuminate\Http\Request $request     Request associated with the endpoint to be cached
     * @param Closure                  $callback    The reponse callback to be cached
     */
    public function remember(Request $request, Closure $callback);
}

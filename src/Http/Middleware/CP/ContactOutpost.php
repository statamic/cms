<?php

namespace Statamic\Http\Middleware\CP;

use Closure;
use Statamic\Licensing\Outpost;

class ContactOutpost
{
    protected $outpost;

    public function __construct(Outpost $outpost)
    {
        $this->outpost = $outpost;
    }

    public function handle($request, Closure $next)
    {
        $this->outpost->radio();

        return $next($request);
    }
}

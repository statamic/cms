<?php

namespace Statamic\Http\Middleware;

use Closure;
use Statamic\API\Str;
use Statamic\Stache\Stache;
use Statamic\Stache\Persister;
use Statamic\Providers\StacheServiceProvider;

class PersistStache
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        return $next($request);
    }

    /**
     * Perform any final actions for the request lifecycle.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Symfony\Component\HttpFoundation\Response  $response
     * @return void
     */
    public function terminate($request, $response)
    {
        // If the Stache was never loaded, don't bother doing anything.
        if (! $this->stacheLoaded()) {
            return;
        }

        // Get the keys of the repos that have been updated. If an aggregate repo
        // was updated, we'll just grab the base repo key (before the ::).
        $updates = app(Stache::class)->updated()->map(function ($key) {
            if (Str::contains($key, '::')) {
                $key = explode('::', $key)[0];
            }

            return $key;
        });

        if ($updates->count()) {
            app(Persister::class)->persist($updates);
        }
    }

    /**
     * Check if the Stache provider was ever registered
     *
     * @return bool
     */
    private function stacheLoaded()
    {
        return in_array(
            StacheServiceProvider::class,
            array_keys(app()->getLoadedProviders())
        );
    }
}

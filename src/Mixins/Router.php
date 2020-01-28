<?php

namespace Statamic\Mixins;

use Statamic\Http\Controllers\FrontendController;

class Router
{
    public function statamic()
    {
        return function ($uri, $view, $data = []) {
            return $this->get($uri, [FrontendController::class, 'route'])
                ->defaults('view', $view)
                ->defaults('data', $data);
        };
    }

    public function amp()
    {
        return function ($routes) {
            $existingRoutes = $this->routes->getRoutesByMethod()['GET'] ?? [];

            $routes($this);

            if (! config('statamic.amp.enabled')) {
                return;
            }

            $updatedRoutes = $this->routes->getRoutesByMethod()['GET'];
            $existingKeys = array_keys($existingRoutes);

            collect($updatedRoutes)->reject(function ($route, $key) use ($existingKeys) {
                return in_array($key, $existingKeys);
            })->each(function ($route) {
                $amp = clone $route;
                $amp->setUri('amp/'.$route->uri());
                if ($amp->getName()) {
                    $amp->name('.amp');
                }
                $this->routes->add($amp);
            });
        };
    }
}

<?php

namespace Statamic\Routing;

class RouteRepository
{
    protected $router;

    public function __construct(Router $router)
    {
        $this->router = $router;
    }

    public function findByUri($uri, $site = null)
    {
        return $this->router->getRoute($uri);
    }
}
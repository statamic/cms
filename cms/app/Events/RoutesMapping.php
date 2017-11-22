<?php

namespace Statamic\Events;

use Illuminate\Routing\Router;

class RoutesMapping extends Event
{
    /**
     * @var Router
     */
    public $router;

    /**
     * @param Router $router
     */
    public function __construct(Router $router)
    {
        $this->router = $router;
    }
}

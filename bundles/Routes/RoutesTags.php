<?php

namespace Statamic\Addons\Routes;

use Statamic\API\URL;
use Statamic\API\Config;
use Statamic\Extend\Tags;

class RoutesTags extends Tags
{
    /**
     * The {{ routes }} tag
     *
     * @return  string
     */
    public function index()
    {
        $rules = Config::get('routes.routes');

        $routes = [];

        foreach ($rules as $url => $route) {

            // Remove any wildcard routes. Ain't
            // nobody need those here.
            if (strpos($url, '{')) continue;

            $data = [
                'url' => $url,
                'permalink' => URL::prependSiteUrl($url)
            ];

            // Simple template routes
            if (! is_array($route)) {
                $routes[] = $data + ['template' => $route];

            // Routes with data
            } else {
                $routes[] = $data + $route;
            }
        }

        return $this->parseLoop($routes);
    }
}

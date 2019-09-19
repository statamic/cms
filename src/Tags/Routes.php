<?php

namespace Statamic\Tags;

use Statamic\Facades\URL;
use Statamic\Tags\Tags;
use Statamic\Facades\Config;

class Routes extends Tags
{
    /**
     * The {{ routes }} tag
     *
     * @return  string
     */
    public function index()
    {
        $rules = Config::get('statamic.routes.routes');

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

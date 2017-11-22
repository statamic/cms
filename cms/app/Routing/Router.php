<?php

namespace Statamic\Routing;

use Statamic\API\URL;

class Router
{
    /**
     * @var array
     */
    private $routes;

    /**
     * @param array $routes
     */
    public function __construct($routes = [])
    {
        $this->routes = $routes;
    }

    /**
     * Attempt to get the data for a route that matches a $url
     *
     * @param string $url  The URL to match against the available route schema.
     * @return Route|null  Either a Route if there's a match, or null if there's no match.
     */
    public function getRoute($url)
    {
        $url = URL::prependSiteUrl($url);

        $routes = $this->standardize($this->routes);

        foreach ($routes as $route => $data) {
            if ($wildcardRoute = $this->getWildcardRoute($route, $url, $data)) {
                return $wildcardRoute;
            }

            if ($exactRoute = $this->getExactRoute($route, $url, $data)) {
                return $exactRoute;
            }
        }
    }

    /**
     * Get a wildcard route
     *
     * @param string $route     The route schema.
     * @param string $url       The URL to match against.
     * @param array $routeData  Any data on the route definition.
     * @return Route|null
     */
    public function getWildcardRoute($route, $url, $routeData)
    {
        // If this route doesn't have any wildcards at all, don't bother going any farther.
        if (! $this->hasWildcard($route)) {
            return null;
        }

        // Convert unnamed wildcards from * to {wildcard_1} etc
        $route = $this->nameUnnamedWildcards($route);

        // Get the wildcard matches from the route. If it isn't a match, we'll just return here.
        if (! $matches = $this->getRouteMatches($route, $url)) {
            return null;
        }

        // Compile an array of data based on the route wildcards and their corresponding url values
        // So for a route of /{foo}/{bar} and a url of /one/two, we'd get [foo=>one, bar=>two]
        $wildcardData = array_combine(
            $this->extractWildcardNames($route),
            $matches
        );

        // Merge in any data defined in the route itself
        $data = array_merge($routeData, $wildcardData);

        return new Route($url, $data);
    }

    /**
     * Get a route based on an exact match
     *
     * @param string $route     The route schema.
     * @param string $url       The URL to match against.
     * @param array $routeData  Any data on the route definition.
     * @return Route|null
     */
    public function getExactRoute($route, $url, $routeData)
    {
        if ($route == $url) {
            return new Route($url, $routeData);
        }
    }

    /**
     * Convert the YAML based routes into a format we need.
     *
     * The routes array is organized with the route as the key and either a string
     * specifying a template, or an array containing data. If just a string was
     * provided, we'll transform it into an array so everything is consistent.
     *
     * @param array $routes
     * @return array
     */
    public function standardize($routes)
    {
        $standardized = [];

        foreach ($routes as $url => $route) {
            if (! is_array($route)) {
                $route = ['template' => $route];
            }

            $standardized[URL::prependSiteUrl($url)] = $route;
        }

        return $standardized;
    }

    /**
     * Check if a given route schema contains any wildcards
     *
     * @param string $route
     * @return bool
     */
    public function hasWildcard($route)
    {
        return $this->hasUnnamedWildcard($route) || $this->hasNamedWildcard($route);
    }

    /**
     * Check if a given route schema contains unnamed wildcards
     *
     * @param string $route
     * @return bool
     */
    public function hasUnnamedWildcard($route)
    {
        return strpos($route, '*') !== false;
    }

    /**
     * Check if a given route schema contains named wildcards
     *
     * @param string $route
     * @return bool
     */
    public function hasNamedWildcard($route)
    {
        return strpos($route, '{') !== false;
    }

    /**
     * Give the unnamed wildcards simple index-based names
     *
     * @param string $route
     * @return string
     */
    public function nameUnnamedWildcards($route)
    {
        $i = 0;

        return preg_replace_callback('/\*/', function ($matches) use (&$i) {
            $i++;
            return "{wildcard_$i}";
        }, $route);
    }

    /**
     * Get an array of the wildcard names from a route schema
     *
     * @param string $route
     * @return array
     */
    public function extractWildcardNames($route)
    {
        preg_match_all('/{\s*([a-zA-Z0-9_\-]+)\s*}/', $route, $matches);

        return $matches[1];
    }

    /**
     * Get an array of wildcard values from a route schema
     *
     * @param string $route
     * @param string $url
     * @return array|false
     */
    public function getRouteMatches($route, $url)
    {
        $regex = preg_replace('/{\s*[a-zA-Z0-9_\-]+\s*}/', '([^/]*)', str_replace('*', '\.', $route));

        if (! preg_match('#^' . $regex . '$#i', $url, $matches)) {
            return false;
        }

        // The first match is the whole URL. Remove it.
        array_shift($matches);

        return $matches;
    }
}

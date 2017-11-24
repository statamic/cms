<?php

namespace Statamic\CP;

use Statamic\API\Str;
use Statamic\API\URL;
use Statamic\API\YAML;
use Statamic\Extend\Addon;
use Statamic\Extend\Management\AddonRepository;
use Illuminate\Routing\Router as LaravelRouter;

class Router
{
    /**
     * @var LaravelRouter
     */
    private $router;

    /**
     * @var AddonRepository
     */
    private $repo;

    /**
     * @param LaravelRouter   $router
     * @param AddonRepository $repo
     */
    public function __construct(LaravelRouter $router, AddonRepository $repo)
    {
        $this->router = $router;
        $this->repo = $repo;
    }

    /**
     * Merge all addon routes into the Laravel router
     */
    public function bindAddonRoutes()
    {
        $this->repo->filename('routes.yaml')->addons()->each(function ($addon) {
            $this->registerRoutes($addon);
        });
    }

    /**
     * Register the routes for a given addon.
     *
     * @param Addon $addon
     */
    private function registerRoutes(Addon $addon)
    {
        $yaml = YAML::parse($addon->getFile('routes.yaml'));

        $routes = array_get($yaml, 'routes', []);

        foreach ($routes as $route => $action) {
            $this->registerRoute($addon, $route, $action);
        }
    }

    /**
     * Register a route
     *
     * @param Addon $addon
     * @param string $route
     * @param string|array $action
     */
    private function registerRoute(Addon $addon, $route, $action)
    {
        // By default, the route will be a GET request.
        $verb = 'get';

        // The http verb may be specified by an @ symbol, eg. post@whatever
        if (Str::contains($route, '@')) {
            list($verb, $route) = explode('@', $route);
        }

        $route = URL::assemble(CP_ROUTE, 'addons', $addon->slug(), $route);

        // The action may just be a string. Normalize it to the array format.
        if (is_string($action)) {
            $action = ['uses' => $action];
        }

        $action['uses'] = $this->getAction($addon, $action['uses']);

        $this->router->$verb($route, $action)->middleware(cp_middleware());
    }

    /**
     * Get the controller class name
     *
     * @param Addon $addon
     * @param string $method
     * @return string
     */
    private function getAction(Addon $addon, $method)
    {
        $namespace = 'Statamic\\Addons\\' . $addon->id() . '\\';

        // Initiall we'll assume the controller will be the default "AddonNameController"
        $controller = $addon->id() . 'Controller';

        // Controllers may be specified with an @ character, eg. MyController@index
        if (Str::contains($method, '@')) {
            list($controller, $method) = explode('@', $method);
        }

        // First check for the controller in the root, eg. Statamic/Addons/AddonName/MyController
        if (class_exists($rootClass = $namespace . $controller)) {
            return $rootClass . '@' . $method;
        }

        // Then check for it within a Controllers namespace.
        // eg. Statamic/Addons/AddonName/Controllers/MyController
        if (class_exists($namespacedClass = $namespace . 'Controllers\\' . $controller)) {
            return $namespacedClass . '@' . $method;
        }

        \Log::debug("Invalid route action: [$rootClass]");
    }
}

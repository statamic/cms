<?php

namespace Statamic\Http\Controllers;

use Statamic\API\URL;
use Statamic\API\Site;
use Statamic\API\Content;
use Statamic\Routing\Router;
use Illuminate\Http\Request;
use Statamic\Exceptions\NotFoundHttpException;

/**
 * The front-end controller
 */
class FrontendController extends Controller
{
    /**
     * @var Router
     */
    private $routeHelper;

    /**
     * Create a new StatamicController
     *
     * @param Router $router
     */
    public function __construct(Router $router)
    {
        $this->routeHelper = $router;
    }

    /**
     * Handles all URLs
     *
     * @return string
     */
    public function index(Request $request)
    {
        $url = $this->removeIgnoredSegments($request->getPathInfo());

        if ($data = $this->getDataForUri($url)) {
            return $data;
        }

        throw new NotFoundHttpException;
    }

    /**
     * Get the data from this URI
     *
     * @param string $uri
     * @return array|bool
     */
    private function getDataForUri($uri)
    {
        $requested_uri = $uri;

        // First we'll attempt to find a matching route.
        if ($route = $this->routeHelper->getRoute($uri)) {
            return $route;
        }

        // Get the default locale's URL for the given current URL.
        if ($default_uri = URL::getDefaultUri(site_locale(), $uri)) {
            $uri = $default_uri;
        }

        // Attempt to get the content at this URI
        if ($content = Content::whereUri($uri)) {
            // Place the content in the locale we want.
            $content = $content->in(site_locale());

            // If the requested URI exists, but also has a localized version, the
            // default URI should not be accessible. For example, if /team has
            // been localized to /equipe, visiting /about should throw a 404.
            if ($requested_uri === $content->uri()) {
                $content->supplementTaxonomies();
                return $content;
            }
        }

        // Still nothing?
        return false;
    }

    public function removeIgnoredSegments($uri)
    {
        $ignore = config('statamic.routes.ignore', []);

        return collect(explode('/', $uri))->reject(function ($segment) use ($ignore) {
            return in_array($segment, $ignore);
        })->implode('/');
    }
}

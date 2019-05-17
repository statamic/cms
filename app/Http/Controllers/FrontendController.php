<?php

namespace Statamic\Http\Controllers;

use Statamic\API\URL;
use Statamic\API\Site;
use Statamic\Statamic;
use Statamic\API\Entry;
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
        $url = Site::current()->relativePath(
            str_finish($request->getUri(), '/')
        );

        if ($url === '') {
            $url = '/';
        }

        if (Statamic::isAmpRequest()) {
            $url = str_after($url, '/' . config('statamic.amp.route'));
        }

        $url = $this->removeIgnoredSegments($url);

        if (str_contains($url, '?')) {
            $url = substr($url, 0, strpos($url, '?'));
        }

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

        // Attempt to get the content at this URI
        if ($content = $this->getContentForUri($uri)) {
            return $content;
        }

        // Still nothing?
        return false;
    }

    private function getContentForUri($uri)
    {
        $site = Site::current()->handle();

        if ($entry = Entry::findByUri($uri, $site)) {
            return $entry->in($site);
        }
    }

    public function removeIgnoredSegments($uri)
    {
        $ignore = config('statamic.routes.ignore', []);

        return collect(explode('/', $uri))->reject(function ($segment) use ($ignore) {
            return in_array($segment, $ignore);
        })->implode('/');
    }
}

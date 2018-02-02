<?php

namespace Statamic\Http\Controllers;

use Statamic\API\Str;
use Statamic\API\URL;
use Statamic\API\Path;
use Statamic\API\User;
use Statamic\API\Event;
use Statamic\API\Site;
use Statamic\Http\View;
use Statamic\API\Config;
use Statamic\API\Folder;
use Statamic\API\Content;
use Illuminate\Http\Request;
use Statamic\Routing\Route;
use Statamic\Routing\Router;
use Statamic\CP\Publish\SneakPeek;
use Statamic\Routing\ExceptionRoute;
use Statamic\Contracts\Data\LocalizedData;
use DebugBar\DataCollector\ConfigCollector;
use Statamic\Auth\Protect\ProtectAPI;

/**
 * The front-end controller
 */
class FrontendController extends Controller
{
    /**
     * @var \Illuminate\Http\Request
     */
    private $request;

    /**
     * @var \Statamic\Contracts\Data\Content\Content
     */
    private $data;

    /**
     * @var mixed
     */
    private $response;

    /**
     * @var bool
     */
    private $peeking;

    /**
     * @var SneakPeek
     */
    private $sneak_peek;

    /**
     * @var Router
     */
    private $routeHelper;

    /**
     * Create a new StatamicController
     *
     * @param \Illuminate\Http\Request $request
     * @param \Statamic\Http\View $view
     * @param Router $router
     */
    public function __construct(Request $request, View $view, Router $router)
    {
        $this->request = $request;
        $this->view = $view;
        $this->routeHelper = $router;
    }

    /**
     * Handles all URLs
     *
     * @return string
     */
    public function index(Request $request)
    {
        // Create a response now so that we can modify it.
        $this->response = response('');

        $site = Site::current();

        $url = $site->relativePath($request->getUri());

        $url = $this->removeIgnoredSegments($url);

        // Are we sneaking a peek?
        if ($this->peeking = $this->request->has('preview')) {
            // Are we allowed to be sneaking a peek?
            if (! User::loggedIn() || ! User::getCurrent()->hasPermission('cp:access')) {
                return $this->notFoundResponse($url);
            }

            $this->sneak_peek = new SneakPeek($this->request);
        }

        // Prevent continuing if we're looking for a missing favicon
        if ($url === '/favicon.ico') {
            return $this->notFoundResponse($url);
        }

        // Attempt to find the data for this URL. It might be content,
        // a route, or nada. If there's nothing, we'll send a 404.
        $this->data = $this->getDataForUri($url);
        if ($this->data === false) {
            return $this->notFoundResponse($url);
        }

        if ($this->data instanceof Route && Str::contains($this->data->template()[0], 'Controller@')) {
            list($controller, $method) = explode('@', $this->data->template()[0]);

            $controller = app()->getNamespace() . "Http\\Controllers\\{$controller}";

            if (! class_exists($controller)) {
                return $this->notFoundResponse($url);
            }

            return app()->call($controller.'@'.$method, []);
        }

        // Check for a redirect within the data
        if ($redirect = $this->getRedirectFromData()) {
            return $redirect;
        }

        // Check for any page protection
        $this->protect();

        // Unpublished content can only be viewed on the front-end if the user has appropriate permission
        if ($this->data instanceof LocalizedData && ! $this->data->published()) {
            $user = User::getCurrent();

            if (! $user || ! $user->hasPermission('content:view_drafts_on_frontend')) {
                return $this->notFoundResponse($url);
            }

            $this->response->header('X-Statamic-Draft', true);
        }

        // If we're sneaking a peek, we'll need to update the data for the content object.
        // At this point, we'll have either an existing content object, or a
        // new temporary one created by the SneakPeek class.
        if ($this->peeking) {
            $data = $this->sneak_peek->update($this->data);
            $this->data->data($data);
        }

        // Load some essential variables that will be available in the template.
        $this->loadKeyVars();

        // Get the output of the parsed template and add it to the response
        $this->response->setContent($this->view->render($this->data));

        $this->setUpDebugBar();

        $this->modifyResponse();

        return $this->response;
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


        // Are we previewing a new page?
        if ($this->peeking) {
            return $this->sneak_peek->content();
        }

        // Still nothing?
        return false;
    }

    /**
     * Get a redirect response from data, if one has been specified using a `redirect` variable.
     *
     * @return null|RedirectResponse
     */
    private function getRedirectFromData()
    {
        if ($redirect = $this->data->get('redirect')) {
            if ($redirect == '404') {
                abort(404);
            }

            return redirect($redirect);
        }
    }

    private function protect()
    {
        return (new ProtectAPI)->protect($this->data);
    }

    public function setUpDebugBar()
    {
        if (! Config::get('debug.debug_bar')) {
            return;
        }

        $data = datastore()->getAll();

        ksort($data);

        debugbar()->addCollector(new ConfigCollector($data, 'Variables'));
    }

    /**
     * @param string $url
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     */
    private function notFoundResponse($url)
    {
        $this->loadKeyVars();

        $template = Str::removeLeft(Path::assemble(config('statamic.theming.errors_directory'), '404'), '/');

        $route = new ExceptionRoute($url, [
            'response_code' => 404,
            'template' => $template
        ]);

        $this->response->setContent($this->view->render($route));
        $this->response->setStatusCode(404);

        $this->setUpDebugBar();

        return $this->response;
    }

    /**
     * Adjust the content type header of the request, if we want something other than HTML.
     */
    private function adjustResponseContentType()
    {
        $content_type = $this->data->get('content_type', 'html');

        // If it's html, we don't need to continue.
        if ($content_type === 'html') {
            return;
        }

        // Translate simple content types to actual ones
        switch ($content_type) {
            case 'xml':
                $content_type = 'text/xml';
                break;
            case 'atom':
                $content_type = 'application/atom+xml; charset=UTF-8';
                break;
            case 'json':
                $content_type = 'application/json';
                break;
            case 'text':
                $content_type = 'text/plain';
        }

        // Adjust the response
        $this->response->header('Content-Type', $content_type);
    }

    /**
     * Modify the Response
     *
     * @return void
     */
    private function modifyResponse()
    {
        // Modify the response if we're attempting to serve something other than just HTML.
        $this->adjustResponseContentType();

        // Add a powered-by header, but only if it's cool with them.
        if (Config::get('statamic.system.send_powered_by_header')) {
            $this->response->header('X-Powered-By', 'Statamic');
        }

        // Allow users to set custom headers
        foreach ($this->data->get('headers', []) as $header => $value) {
            $this->response->header($header, $value);
        }

        // Allow addons to modify the response. They can add headers, modify the content, etc.
        // The event will get the Response object as a payload, which they simply need to modify.
        event('response.created', $this->response);
    }

    public function removeIgnoredSegments($uri)
    {
        $ignore = config('statamic.routes.ignore', []);

        return collect(explode('/', $uri))->reject(function ($segment) use ($ignore) {
            return in_array($segment, $ignore);
        })->implode('/');
    }
}

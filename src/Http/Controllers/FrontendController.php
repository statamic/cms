<?php

namespace Statamic\Http\Controllers;

use Illuminate\Http\Request;
use Statamic\Exceptions\NotFoundHttpException;
use Statamic\Facades\Content;
use Statamic\Facades\Data;
use Statamic\Facades\Site;
use Statamic\Facades\URL;
use Statamic\Statamic;
use Statamic\View\View;

/**
 * The front-end controller
 */
class FrontendController extends Controller
{
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

        if (str_contains($url, '?')) {
            $url = substr($url, 0, strpos($url, '?'));
        }

        if ($data = Data::findByUri($url, Site::current()->handle())) {
            return $data;
        }

        throw new NotFoundHttpException;
    }

    public function route(...$args)
    {
        [$view, $data] = array_slice($args, -2);

        return (new View)
            ->template($view)
            ->layout($data['layout'] ?? 'layout')
            ->with($data)
            ->cascadeContent($this->getLoadedRouteItem($data));
    }

    private function getLoadedRouteItem($data)
    {
        if (! $item = $data['load'] ?? null) {
            return null;
        }

        if ($data = Data::find($item)) {
            return $data;
        }

        if ($data = Data::findByUri($item)) {
            return $data;
        }
    }
}

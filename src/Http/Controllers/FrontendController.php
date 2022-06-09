<?php

namespace Statamic\Http\Controllers;

use Illuminate\Http\Request;
use Statamic\Exceptions\NotFoundHttpException;
use Statamic\Facades\Data;
use Statamic\Facades\Site;
use Statamic\Http\Responses\DataResponse;
use Statamic\Statamic;
use Statamic\Support\Arr;
use Statamic\Support\Str;
use Statamic\View\View;

/**
 * The front-end controller.
 */
class FrontendController extends Controller
{
    public function __construct()
    {
        $this->middleware('statamic.web');
    }

    /**
     * Handles all URLs.
     *
     * @return string
     */
    public function index(Request $request)
    {
        $url = Site::current()->relativePath($request->getUri());

        if (Statamic::isAmpRequest()) {
            $url = Str::ensureLeft(Str::after($url, '/'.config('statamic.amp.route')), '/');
        }

        if (Str::contains($url, '?')) {
            $url = substr($url, 0, strpos($url, '?'));
        }

        if (Str::endsWith($url, '/') && Str::length($url) > 1) {
            $url = rtrim($url, '/');
        }

        if ($data = Data::findByUri($url, Site::current()->handle())) {
            return $data;
        }

        throw new NotFoundHttpException;
    }

    public function route(Request $request, ...$args)
    {
        $params = $request->route()->parameters();
        $view = Arr::pull($params, 'view');
        $data = Arr::pull($params, 'data');
        $data = array_merge($params, $data);

        $view = (new View)
            ->template($view)
            ->layout(Arr::get($data, 'layout', 'layout'))
            ->with($data)
            ->cascadeContent($this->getLoadedRouteItem($data));

        $contentType = DataResponse::contentType(
            $data['content_type']
            ?? ($view->wantsXmlResponse() ? 'xml' : 'html')
        );

        return response($view->render(), 200, [
            'Content-Type' => $contentType,
        ]);
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

<?php

namespace Statamic\Http\Controllers;

use Illuminate\Contracts\View\View as IlluminateView;
use Illuminate\Http\Request;
use Statamic\Auth\Protect\Protection;
use Statamic\Exceptions\NotFoundHttpException;
use Statamic\Facades\Data;
use Statamic\Http\Responses\DataResponse;
use Statamic\Support\Arr;
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
        if ($data = Data::findByRequestUrl($request->url())) {
            return $data;
        }

        app(Protection::class)->protect();

        throw new NotFoundHttpException;
    }

    public function route(Request $request, ...$args)
    {
        $params = $request->route()->parameters();

        $view = Arr::pull($params, 'view');
        $data = Arr::pull($params, 'data');

        $data = array_merge($params, ray()->pass(is_callable($data) ? $data(...$params) : $data));

        if (is_callable($view)) {
            $view = $view(...$params);
        }

        if ($view instanceof IlluminateView) {
            $data = array_merge($view->getData(), $data);
            $view = $view->name();
        } elseif (! is_string($view)) {
            // TODO: What else can they return from view closure?
        }

        $view = app(View::class)
            ->template($view)
            ->layout(Arr::get($data, 'layout', config('statamic.system.layout', 'layout')))
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

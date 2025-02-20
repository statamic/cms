<?php

namespace Statamic\Http\Controllers;

use Closure;
use Illuminate\Contracts\View\View as IlluminateView;
use Illuminate\Http\Request;
use ReflectionFunction;
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

        throw_if(is_callable($view) && $data, new \Exception('Parameter [$data] not supported with [$view] closure!'));

        if (is_callable($view)) {
            $resolvedView = static::resolveRouteClosure($view, $params);
        }

        if (isset($resolvedView) && $resolvedView instanceof IlluminateView) {
            $view = $resolvedView->name();
            $data = $resolvedView->getData();
        } elseif (isset($resolvedView)) {
            return $resolvedView;
        }

        $data = array_merge($params, is_callable($data)
            ? static::resolveRouteClosure($data, $params)
            : $data);

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

    private static function resolveRouteClosure(Closure $closure, array $params)
    {
        $reflect = new ReflectionFunction($closure);

        $params = collect($reflect->getParameters())
            ->map(fn ($param) => $param->hasType() ? app($param->getType()->getName()) : $params[$param->getName()])
            ->all();

        return $closure(...$params);
    }
}

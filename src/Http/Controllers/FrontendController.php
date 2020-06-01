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
        $url = Site::current()->relativePath(
            str_finish($request->getUri(), '/')
        );

        if ($url === '') {
            $url = '/';
        }

        if (Statamic::isAmpRequest()) {
            $url = str_after($url, '/'.config('statamic.amp.route'));
        }

        if (Str::contains($url, '?')) {
            $url = substr($url, 0, strpos($url, '?'));
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

        $this->addViewPaths();

        $contents = (new View)
            ->template($view)
            ->layout(Arr::get($data, 'layout', 'layout'))
            ->with($data)
            ->cascadeContent($this->getLoadedRouteItem($data))
            ->render();

        return response($contents, 200, [
            'Content-Type' => DataResponse::contentType($data['content_type'] ?? 'html'),
        ]);
    }

    protected function addViewPaths()
    {
        $finder = view()->getFinder();
        $amp = Statamic::isAmpRequest();
        $site = Site::current()->handle();

        $paths = collect($finder->getPaths())->flatMap(function ($path) use ($site, $amp) {
            return [
                $amp ? $path.'/'.$site.'/amp' : null,
                $path.'/'.$site,
                $amp ? $path.'/amp' : null,
                $path,
            ];
        })->filter()->values()->all();

        $finder->setPaths($paths);

        return $this;
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

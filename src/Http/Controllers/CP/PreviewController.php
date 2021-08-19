<?php

namespace Statamic\Http\Controllers\CP;

use Exception;
use Facades\Statamic\View\Cascade;
use Illuminate\Contracts\Debug\ExceptionHandler;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Facade;
use Statamic\Facades\Site;
use Symfony\Component\Debug\Exception\FatalThrowableError;
use Symfony\Component\HttpFoundation\Request as SymfonyRequest;
use Throwable;

class PreviewController extends CpController
{
    public function show()
    {
        return view('statamic::entries.preview');
    }

    public function edit(Request $request, $_, $data)
    {
        $this->authorize('view', $data);

        $fields = $data->blueprint()
            ->fields()
            ->addValues($request->input('preview', []))
            ->process();

        foreach (array_except($fields->values()->all(), ['slug']) as $key => $value) {
            $data->setSupplement($key, $value);
        }

        return $this->getDataResponse($request, $data)->getContent();
    }

    protected function getDataResponse($request, $data)
    {
        $url = $request->amp ? $data->ampUrl() : $data->absoluteUrl();

        $subrequest = Request::createFromBase(SymfonyRequest::create($url));

        $subrequest->headers->set('X-Statamic-Live-Preview', true);

        app()->instance('request', $subrequest);
        Facade::clearResolvedInstance('request');
        Cascade::withRequest($subrequest);
        Cascade::withSite(Site::current());
        app('translator')->setLocale(Site::current()->shortLocale());

        try {
            $response = $data->toLivePreviewResponse($subrequest, $request->extras);
        } catch (Exception $e) {
            app(ExceptionHandler::class)->report($e);
            $response = app(ExceptionHandler::class)->render($subrequest, $e);
        } catch (Throwable $e) {
            app(ExceptionHandler::class)->report($e = new FatalThrowableError($e));
            $response = app(ExceptionHandler::class)->render($subrequest, $e);
        }

        app()->instance('request', $request);
        Facade::clearResolvedInstance('request');
        Cascade::withRequest($request);
        Cascade::withSite(Site::current());
        app('translator')->setLocale(Site::current()->shortLocale());

        return $response;
    }
}

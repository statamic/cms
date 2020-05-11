<?php

namespace Statamic\Http\Responses;

use Facades\Statamic\View\Cascade;
use Illuminate\Contracts\Support\Responsable;
use Statamic\Auth\Protect\Protection;
use Statamic\Events\ResponseCreated;
use Statamic\Exceptions\NotFoundHttpException;
use Statamic\Facades\Site;
use Statamic\Routing\ResolveRedirect;
use Statamic\Statamic;
use Statamic\View\View;

class DataResponse implements Responsable
{
    protected $data;
    protected $request;
    protected $headers = [];
    protected $with = [];

    public function __construct($data)
    {
        $this->data = $data;
    }

    public function toResponse($request)
    {
        $this->request = $request;

        if ($redirect = $this->getRedirect()) {
            return $redirect;
        }

        $this
            ->protect()
            ->handleDraft()
            ->handlePrivateEntries()
            ->adjustResponseType()
            ->addContentHeaders()
            ->addViewPaths()
            ->handleAmp();

        $response = response()
            ->make($this->contents())
            ->withHeaders($this->headers);

        ResponseCreated::dispatch($response);

        return $response;
    }

    protected function addViewPaths()
    {
        $finder = view()->getFinder();
        $amp = Statamic::isAmpRequest();

        $site = method_exists($this->data, 'site')
            ? $this->data->site()->handle()
            : Site::current()->handle();

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

    protected function handleAmp()
    {
        if (Statamic::isAmpRequest() && ! $this->data->ampable()) {
            abort(404);
        }

        return $this;
    }

    protected function getRedirect()
    {
        if (! $redirect = $this->data->get('redirect')) {
            return;
        }

        $redirect = (new ResolveRedirect)($redirect, $this->data);

        if ($redirect == '404') {
            throw new NotFoundHttpException;
        }

        return redirect($redirect);
    }

    protected function protect()
    {
        app(Protection::class)
            ->setData($this->data)
            ->protect();

        return $this;
    }

    protected function handleDraft()
    {
        if (! method_exists($this->data, 'published')) {
            return $this;
        }

        if (! $this->isLivePreview() && ! $this->data->published()) {
            throw new NotFoundHttpException;
        }

        if ($this->isLivePreview()) {
            $this->headers['X-Statamic-Draft'] = true;
        }

        return $this;
    }

    protected function handlePrivateEntries()
    {
        if (! method_exists($this->data, 'private')) {
            return $this;
        }

        throw_if($this->data->private(), new NotFoundHttpException);

        return $this;
    }

    protected function contents()
    {
        return (new View)
            ->template($this->data->template())
            ->layout($this->data->layout())
            ->with($this->with)
            ->cascadeContent($this->data)
            ->render();
    }

    protected function cascade()
    {
        return Cascade::instance()->withContent($this->data)->hydrate();
    }

    protected function adjustResponseType()
    {
        $contentType = $this->data->get('content_type', 'html');

        if ($contentType !== 'html') {
            $this->headers['Content-Type'] = self::contentType($contentType);
        }

        return $this;
    }

    protected function addContentHeaders()
    {
        foreach ($this->data->get('headers', []) as $header => $value) {
            $this->headers[$header] = $value;
        }

        return $this;
    }

    public function with($data)
    {
        $this->with = $data;

        return $this;
    }

    protected function isLivePreview()
    {
        return $this->request->headers->get('X-Statamic-Live-Preview');
    }

    public static function contentType($type)
    {
        switch ($type) {
            case 'html':
                return 'text/html; charset=UTF-8';
            case 'xml':
                return 'text/xml';
            case 'rss':
                return 'application/rss+xml';
            case 'atom':
                return 'application/atom+xml; charset=UTF-8';
            case 'json':
                return 'application/json';
            case 'text':
                return 'text/plain';
            default:
                return $type;
        }
    }
}

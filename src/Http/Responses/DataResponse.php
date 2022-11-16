<?php

namespace Statamic\Http\Responses;

use Illuminate\Contracts\Support\Responsable;
use Illuminate\Support\Str;
use Statamic\Auth\Protect\Protection;
use Statamic\Events\ResponseCreated;
use Statamic\Exceptions\NotFoundHttpException;
use Statamic\Facades\Site;
use Statamic\Statamic;
use Statamic\Tokens\Handlers\LivePreview;
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
            ->handleAmp();

        $response = response()
            ->make($this->contents())
            ->withHeaders($this->headers);

        ResponseCreated::dispatch($response, $this->data);

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
        if (! $this->data->get('redirect')) {
            return;
        }

        if (! $redirect = $this->data->redirect) {
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

        if ($this->data->published()) {
            return $this;
        }

        throw_unless($this->isLivePreview(), new NotFoundHttpException);

        $this->headers['X-Statamic-Draft'] = true;

        return $this;
    }

    protected function handlePrivateEntries()
    {
        if (! method_exists($this->data, 'private')) {
            return $this;
        }

        if (! $this->data->private()) {
            return $this;
        }

        throw_unless($this->isLivePreview(), new NotFoundHttpException);

        $this->headers['X-Statamic-Private'] = true;

        return $this;
    }

    protected function view()
    {
        return app(View::class)
            ->template($this->data->template())
            ->layout($this->data->layout())
            ->with($this->with)
            ->cascadeContent($this->data);
    }

    protected function contents()
    {
        $contents = $this->view()->render();

        if ($this->isLivePreview()) {
            $contents = $this->versionJavascriptModules($contents);
        }

        return $contents;
    }

    protected function adjustResponseType()
    {
        $contentType = $this->data->get(
            'content_type',
            $this->view()->wantsXmlResponse() ? 'xml' : 'html'
        );

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
        return optional($this->request->statamicToken())->handler() === LivePreview::class;
    }

    protected function versionJavascriptModules($contents)
    {
        return preg_replace_callback('~<script[^>]*type=("|\')module\1[^>]*>~i', function ($scriptMatches) {
            return preg_replace_callback('~src=("|\')(.*?)\1~i', function ($matches) {
                $quote = $matches[1];
                $url = $matches[2];

                $parameter = 't='.(microtime(true) * 10000);

                if (Str::contains($url, '?')) {
                    $url = str_replace('?', "?$parameter&", $url);
                } else {
                    $url .= "?$parameter";
                }

                return 'src='.$quote.$url.$quote;
            }, $scriptMatches[0]);
        }, $contents);
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

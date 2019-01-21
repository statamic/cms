<?php

namespace Statamic\Http\Responses;

use Statamic\View\Antlers\View;
use Facades\Statamic\View\Cascade;
use Statamic\Events\ResponseCreated;
use Statamic\Auth\Protect\Protection;
use Illuminate\Contracts\Support\Responsable;
use Statamic\Exceptions\NotFoundHttpException;

class DataResponse implements Responsable
{
    protected $data;
    protected $request;
    protected $headers = [];

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
            ->handleLivePreview()
            ->adjustResponseType()
            ->addContentHeaders();

        $response = response()
            ->make($this->contents())
            ->withHeaders($this->headers);

        ResponseCreated::dispatch($response);

        return $response;
    }

    protected function getRedirect()
    {
        if (! $redirect = $this->data->get('redirect')) {
            return;
        }

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
        if ($this->data->published()) {
            return $this;
        }

        $user = optional($this->request->user());

        if (! $user->can('view drafts on frontend')) {
            throw new NotFoundHttpException;
        }

        $this->headers['X-Statamic-Draft'] = true;

        return $this;
    }

    protected function handleLivePreview()
    {
        // todo

        return $this;
    }

    protected function contents()
    {
        return (new View)
            ->template($this->data->template())
            ->layout($this->data->layout())
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

        // If it's html, we don't need to continue.
        if ($contentType === 'html') {
            return $this;
        }

        // Translate simple content types to actual ones
        switch ($contentType) {
            case 'xml':
                $contentType = 'text/xml';
                break;
            case 'rss':
                $contentType = 'application/rss+xml';
                break;
            case 'atom':
                $contentType = 'application/atom+xml; charset=UTF-8';
                break;
            case 'json':
                $contentType = 'application/json';
                break;
            case 'text':
                $contentType = 'text/plain';
        }

        $this->headers['Content-Type'] = $contentType;

        return $this;
    }

    protected function addContentHeaders()
    {
        foreach ($this->data->get('headers', []) as $header => $value) {
            $this->headers[$header] = $value;
        }

        return $this;
    }
}

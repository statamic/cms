<?php

namespace Statamic\Routing;

use Statamic\API\URL;
use Statamic\API\Site;
use Statamic\API\Config;
use Statamic\Http\Responses\DataResponse;
use Illuminate\Contracts\Support\Responsable;

class Route implements Responsable
{
    private $uri;
    private $data;

    public function __construct($uri, $data)
    {
        $this->uri = $uri;
        $this->data = $data;
    }

    public function get($key, $default = null)
    {
        return array_get($this->data, $key, $default);
    }

    public function template()
    {
        return $this->get('template', config('statamic.theming.views.default'));
    }

    public function layout()
    {
        return $this->get('layout', config('statamic.theming.views.layout'));
    }

    public function url()
    {
        return e($this->uri);
    }

    public function absoluteUrl()
    {
        return URL::makeAbsolute($this->url());
    }

    public function toArray()
    {
        return array_merge($this->data, $this->loadedData(), [
            'url' => $this->url(),
            'amp_url' => $this->ampUrl(),
            'permalink' => $this->absoluteUrl(),
        ]);
    }

    public function loadedData()
    {
        if (! $load = array_get($this->data, 'load')) {
            return [];
        }

        if ($content = Content::find($load)) {
            return $content->toArray();
        }

        if ($content = Content::whereUri($load)) {
            return $content->toArray();
        }

        return [];
    }

    public function published()
    {
        return true;
    }

    public function toResponse($request)
    {
        return (new DataResponse($this))->toResponse($request);
    }

    public function ampable()
    {
        return $this->get('amp');
    }

    public function ampUrl()
    {
        return !$this->ampable() ? null : vsprintf('%s/%s/%s', [
            rtrim($this->site()->absoluteUrl(), '/'),
            config('statamic.amp.route'),
            ltrim($this->uri, '/')
        ]);
    }

    public function site()
    {
        return Site::current();
    }
}

<?php

namespace Statamic\Routing;

use Statamic\API\URL;
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

    /**
     * @return array
     */
    public function template()
    {
        return [
            $this->get('template'),
            config('statamic.theming.views.default')
        ];
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
}

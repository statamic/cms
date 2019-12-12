<?php

namespace Statamic\Routing;

use Statamic\Facades\URL;
use Statamic\Facades\Site;
use Statamic\Facades\Entry;
use Statamic\Facades\Config;
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
        return $this->get('template', 'default');
    }

    public function layout()
    {
        return $this->get('layout', 'layout');
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

        if ($content = $this->getItem($load)) {
            return $content->toAugmentedArray();
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

    public function private()
    {
        return false;
    }

    public function site()
    {
        return Site::current();
    }

    protected function getItem($item)
    {
        if ($entry = Entry::find($item)) {
            return $entry;
        }

        if ($entry = Entry::findByUri($item)) {
            return $entry;
        }
    }
}

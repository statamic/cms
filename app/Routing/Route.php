<?php

namespace Statamic\Routing;

use Statamic\API\Config;
use Statamic\API\URL;

class Route
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
            config('theming.views.default')
        ];
    }

    public function layout()
    {
        return $this->get('layout', config('theming.views.layout'));
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
        return array_merge($this->data, [
            'url' => $this->url(),
            'permalink' => $this->absoluteUrl(),
        ]);
    }
}

<?php

namespace Statamic\Addons\Suggest;

/**
 * Adapts suggest mode data into an object that resembles the basics
 * of Illuminate\Http\Request for backwards compatability.
 *
 * Suggest mode classes may have relied on $this->request to get AJAX POST'ed config options.
 * Since 2.7, we prefetch suggestions without an AJAX request and pass in the config options
 * directly. This class lets suggest modes continue to function by using $this->request.
 */
class RequestAdapter
{
    protected $data;

    public function __construct($data)
    {
        $this->data = $data;
    }

    public function input($key, $default = null)
    {
        return array_get($this->data, $key, $default);
    }

    public function __get($key)
    {
        return $this->input($key);
    }
}

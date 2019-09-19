<?php

namespace Statamic\Auth\Protect\Protectors;

abstract class Protector
{
    protected $url;
    protected $data;
    protected $scheme;
    protected $config;

    public function setUrl($url)
    {
        $this->url = $url;

        return $this;
    }

    public function setData($data)
    {
        $this->data = $data;

        return $this;
    }

    public function setScheme($scheme)
    {
        $this->scheme = $scheme;

        return $this;
    }

    public function setConfig($config)
    {
        $this->config = $config;

        return $this;
    }
}

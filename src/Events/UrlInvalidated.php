<?php

namespace Statamic\Events;

class UrlInvalidated extends Event
{
    public $domain;
    public $fullUrl;
    public $url;

    public function __construct($url, $domain = null)
    {
        $this->domain = $domain;
        $this->fullUrl = url($url);
        $this->url = $url;
    }
}

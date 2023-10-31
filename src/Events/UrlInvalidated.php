<?php

namespace Statamic\Events;

class UrlInvalidated extends Event
{
    public $domain;
    public $url;

    public function __construct($url, $domain = null)
    {
        $this->domain = $domain;
        $this->url = $url;
    }
}

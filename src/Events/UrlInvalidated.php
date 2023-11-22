<?php

namespace Statamic\Events;

class UrlInvalidated extends Event
{
    public $url;

    public function __construct($url, $domain = null)
    {
        $this->url = $domain.$url;
    }
}

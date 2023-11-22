<?php

namespace Statamic\Events;

use Statamic\StaticCaching\Cacher;

class UrlInvalidated extends Event
{
    public $url;

    public function __construct($url, $domain = null)
    {
        $domain ??= app(Cacher::class)->getBaseUrl();

        $this->url = $domain.$url;
    }
}

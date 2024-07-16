<?php

namespace Statamic\Events;

use Statamic\Sites\Site;

class SiteSaved extends Event
{
    public function __construct(public Site $site)
    {
        //
    }
}

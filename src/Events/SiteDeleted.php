<?php

namespace Statamic\Events;

use Statamic\Sites\Site;

class SiteDeleted extends Event
{
    public function __construct(public Site $site)
    {
        //
    }
}

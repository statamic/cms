<?php

namespace Statamic\Events;

use Statamic\Sites\Site;

class SiteCreated extends Event
{
    public function __construct(public Site $site)
    {
    }
}

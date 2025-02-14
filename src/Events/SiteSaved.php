<?php

namespace Statamic\Events;

use Statamic\Contracts\Git\ProvidesCommitMessage;
use Statamic\Sites\Site;

class SiteSaved extends Event implements ProvidesCommitMessage
{
    public function __construct(public Site $site)
    {
    }

    public function commitMessage()
    {
        return __('Site saved', [], config('statamic.git.locale'));
    }
}

<?php

namespace Statamic\Events;

use Statamic\Contracts\Git\ProvidesCommitMessage;
use Statamic\Sites\Site;

use function Statamic\trans as __;

class SiteDeleted extends Event implements ProvidesCommitMessage
{
    public function __construct(public Site $site)
    {
    }

    public function commitMessage()
    {
        return __('Site deleted', [], config('statamic.git.locale'));
    }
}

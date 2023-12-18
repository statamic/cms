<?php

namespace Statamic\Events;

use Statamic\Contracts\Git\ProvidesCommitMessage;
use Statamic\Events\Concerns\TracksAuthenticatedUser;

class AssetContainerSaved extends Event implements ProvidesCommitMessage
{
    use TracksAuthenticatedUser;

    public $container;

    public function __construct($container)
    {
        $this->container = $container;
    }

    public function commitMessage()
    {
        return __('Asset container saved', [], config('statamic.git.locale'));
    }
}

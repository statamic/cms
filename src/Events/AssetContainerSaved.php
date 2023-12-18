<?php

namespace Statamic\Events;

use Statamic\Contracts\Git\ProvidesCommitMessage;

class AssetContainerSaved extends Event implements ProvidesCommitMessage
{
    public $container;
    public $currentUser;

    public function __construct($container, $currentUser = null)
    {
        $this->container = $container;
        $this->currentUser = $currentUser;
    }

    public function commitMessage()
    {
        return __('Asset container saved', [], config('statamic.git.locale'));
    }
}

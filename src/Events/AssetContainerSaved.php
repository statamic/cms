<?php

namespace Statamic\Events;

use Statamic\Contracts\Git\ProvidesCommitMessage;

class AssetContainerSaved extends Event implements ProvidesCommitMessage
{
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

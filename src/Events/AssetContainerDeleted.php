<?php

namespace Statamic\Events;

use Statamic\Contracts\Git\ProvidesCommitMessage;

class AssetContainerDeleted extends Event implements ProvidesCommitMessage
{
    public $container;

    public function __construct($container)
    {
        $this->container = $container;
    }

    public function commitMessage()
    {
        return __('Asset container deleted', [], config('statamic.git.locale'));
    }
}

<?php

namespace Statamic\Events;

use Statamic\Contracts\Git\ProvidesCommitMessage;

class AssetContainerSaved extends Event implements ProvidesCommitMessage
{
    public function __construct(public $container)
    {
    }

    public function commitMessage()
    {
        return __('Asset container saved', [], config('statamic.git.locale'));
    }
}

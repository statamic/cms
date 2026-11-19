<?php

namespace Statamic\Events;

use Statamic\Contracts\Git\ProvidesCommitMessage;

use function Statamic\trans as __;

class AssetContainerDeleted extends Event implements ProvidesCommitMessage
{
    public function __construct(public $container)
    {
    }

    public function commitMessage()
    {
        return __('Asset container deleted', [], config('statamic.git.locale'));
    }
}

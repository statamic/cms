<?php

namespace Statamic\Events;

use Statamic\Contracts\Git\ProvidesCommitMessage;

class AssetDeleted extends Event implements ProvidesCommitMessage
{
    public function __construct(public $asset)
    {
    }

    public function commitMessage()
    {
        return __('Asset deleted', [], config('statamic.git.locale'));
    }
}

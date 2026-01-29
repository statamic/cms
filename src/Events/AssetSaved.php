<?php

namespace Statamic\Events;

use Statamic\Contracts\Git\ProvidesCommitMessage;

class AssetSaved extends Event implements ProvidesCommitMessage
{
    public function __construct(public $asset)
    {
    }

    public function commitMessage()
    {
        return __('Asset saved', [], config('statamic.git.locale'));
    }
}

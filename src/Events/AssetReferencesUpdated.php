<?php

namespace Statamic\Events;

use Statamic\Contracts\Git\ProvidesCommitMessage;

class AssetReferencesUpdated extends Event implements ProvidesCommitMessage
{
    public function __construct(public $asset)
    {
    }

    public function commitMessage()
    {
        return __('Asset references updated', [], config('statamic.git.locale'));
    }
}

<?php

namespace Statamic\Events;

use Statamic\Contracts\Git\ProvidesCommitMessage;

class AssetUploaded extends Event implements ProvidesCommitMessage
{
    public function __construct(public $asset, public $originalFilename)
    {
    }

    public function commitMessage()
    {
        return __('Asset uploaded', [], config('statamic.git.locale'));
    }
}

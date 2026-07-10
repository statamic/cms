<?php

namespace Statamic\Events;

use Statamic\Contracts\Git\ProvidesCommitMessage;

use function Statamic\trans as __;

class AssetReuploaded extends Event implements ProvidesCommitMessage
{
    public function __construct(public $asset, public $originalFilename)
    {
    }

    public function commitMessage()
    {
        return __('Asset reuploaded', [], config('statamic.git.locale'));
    }
}

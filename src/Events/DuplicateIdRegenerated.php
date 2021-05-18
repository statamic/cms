<?php

namespace Statamic\Events;

use Statamic\Contracts\Git\ProvidesCommitMessage;

class DuplicateIdRegenerated extends Event implements ProvidesCommitMessage
{
    public function commitMessage()
    {
        return __('Duplicate ID Regenerated', [], config('statamic.git.locale'));
    }
}

<?php

namespace Statamic\Events;

use Statamic\Contracts\Git\ProvidesCommitMessage;

use function Statamic\trans as __;

class DuplicateIdRegenerated extends Event implements ProvidesCommitMessage
{
    public function commitMessage()
    {
        return __('Duplicate ID Regenerated', [], config('statamic.git.locale'));
    }
}

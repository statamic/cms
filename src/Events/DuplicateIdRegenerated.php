<?php

namespace Statamic\Events;

use Statamic\Contracts\Git\ProvidesCommitMessage;
use Statamic\Events\Concerns\TracksAuthenticatedUser;

class DuplicateIdRegenerated extends Event implements ProvidesCommitMessage
{
    use TracksAuthenticatedUser;

    public function commitMessage()
    {
        return __('Duplicate ID Regenerated', [], config('statamic.git.locale'));
    }
}

<?php

namespace Statamic\Events;

use Statamic\Contracts\Git\ProvidesCommitMessage;

use function Statamic\trans as __;

class RevisionDeleted extends Event implements ProvidesCommitMessage
{
    public function __construct(public $revision)
    {
    }

    public function commitMessage()
    {
        return __('Revision deleted', [], config('statamic.git.locale'));
    }
}

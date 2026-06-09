<?php

namespace Statamic\Events;

use Statamic\Contracts\Git\ProvidesCommitMessage;

use function Statamic\trans as __;

class SubmissionDeleted extends Event implements ProvidesCommitMessage
{
    public function __construct(public $submission)
    {
    }

    public function commitMessage()
    {
        return __('Submission deleted', [], config('statamic.git.locale'));
    }
}

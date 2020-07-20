<?php

namespace Statamic\Events;

class SubmissionDeleted extends Deleted
{
    public function commitMessage()
    {
        return __('Submission deleted');
    }
}

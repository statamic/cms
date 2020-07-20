<?php

namespace Statamic\Events;

class SubmissionSaved extends Saved
{
    public function commitMessage()
    {
        return __('Submission saved');
    }
}

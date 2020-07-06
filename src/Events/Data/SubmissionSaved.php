<?php

namespace Statamic\Events\Data;

class SubmissionSaved extends Saved
{
    public function commitMessage()
    {
        return __('Submission saved');
    }
}

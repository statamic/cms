<?php

namespace Statamic\Events\Data;

class SubmissionDeleted extends Deleted
{
    public function commitMessage()
    {
        return __('Submission deleted');
    }
}

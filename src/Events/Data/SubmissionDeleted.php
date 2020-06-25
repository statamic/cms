<?php

namespace Statamic\Events\Data;

class SubmissionDeleted extends Deleted
{
    /**
     * To sentence.
     *
     * @return string
     */
    public function toSentence()
    {
        return __('Submission deleted.');
    }
}

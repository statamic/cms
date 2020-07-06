<?php

namespace Statamic\Events\Data;

class SubmissionSaved extends Saved
{
    /**
     * To sentence.
     *
     * @return string
     */
    public function toSentence()
    {
        return __('Submission saved');
    }
}

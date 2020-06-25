<?php

namespace Statamic\Events\Data;

class FormDeleted extends Deleted
{
    /**
     * To sentence.
     *
     * @return string
     */
    public function toSentence()
    {
        return __('Form deleted.');
    }
}

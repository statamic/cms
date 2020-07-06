<?php

namespace Statamic\Events\Data;

class FormSaved extends Saved
{
    /**
     * To sentence.
     *
     * @return string
     */
    public function toSentence()
    {
        return __('Form saved');
    }
}

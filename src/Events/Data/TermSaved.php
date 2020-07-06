<?php

namespace Statamic\Events\Data;

class TermSaved extends Saved
{
    /**
     * To sentence.
     *
     * @return string
     */
    public function toSentence()
    {
        return __('Term saved');
    }
}

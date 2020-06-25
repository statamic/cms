<?php

namespace Statamic\Events\Data;

class GlobalSetSaved extends Saved
{
    /**
     * To sentence.
     *
     * @return string
     */
    public function toSentence()
    {
        return __('Global set saved.');
    }
}

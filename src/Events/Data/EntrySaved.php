<?php

namespace Statamic\Events\Data;

class EntrySaved extends Saved
{
    /**
     * To sentence.
     *
     * @return string
     */
    public function toSentence()
    {
        return __('Entry saved');
    }
}

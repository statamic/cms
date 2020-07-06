<?php

namespace Statamic\Events\Data;

class EntryDeleted extends Deleted
{
    /**
     * To sentence.
     *
     * @return string
     */
    public function toSentence()
    {
        return __('Entry deleted');
    }
}

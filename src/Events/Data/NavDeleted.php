<?php

namespace Statamic\Events\Data;

class NavDeleted extends Deleted
{
    /**
     * To sentence.
     *
     * @return string
     */
    public function toSentence()
    {
        return __('Navigation deleted.');
    }
}

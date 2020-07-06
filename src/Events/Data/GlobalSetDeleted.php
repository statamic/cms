<?php

namespace Statamic\Events\Data;

class GlobalSetDeleted extends Deleted
{
    /**
     * To sentence.
     *
     * @return string
     */
    public function toSentence()
    {
        return __('Global Set deleted');
    }
}

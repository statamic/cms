<?php

namespace Statamic\Events\Data;

class TermDeleted extends Deleted
{
    /**
     * To sentence.
     *
     * @return string
     */
    public function toSentence()
    {
        return __('Term deleted.');
    }
}

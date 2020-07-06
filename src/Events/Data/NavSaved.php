<?php

namespace Statamic\Events\Data;

class NavSaved extends Saved
{
    /**
     * To sentence.
     *
     * @return string
     */
    public function toSentence()
    {
        return __('Navigation saved');
    }
}

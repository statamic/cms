<?php

namespace Statamic\Events\Data;

class RoleSaved extends Saved
{
    /**
     * To sentence.
     *
     * @return string
     */
    public function toSentence()
    {
        return __('Role saved');
    }
}

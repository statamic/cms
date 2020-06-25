<?php

namespace Statamic\Events\Data;

class UserDeleted extends Deleted
{
    /**
     * To sentence.
     *
     * @return string
     */
    public function toSentence()
    {
        return __('User deleted.');
    }
}

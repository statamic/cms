<?php

namespace Statamic\Events\Data;

class UserGroupDeleted extends Deleted
{
    /**
     * To sentence.
     *
     * @return string
     */
    public function toSentence()
    {
        return __('User group deleted');
    }
}

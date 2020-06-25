<?php

namespace Statamic\Events\Data;

class UserGroupSaved extends Saved
{
    /**
     * To sentence.
     *
     * @return string
     */
    public function toSentence()
    {
        return __('User group saved.');
    }
}

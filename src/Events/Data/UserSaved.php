<?php

namespace Statamic\Events\Data;

class UserSaved extends Saved
{
    /**
     * To sentence.
     *
     * @return string
     */
    public function toSentence()
    {
        return __('User saved.');
    }
}

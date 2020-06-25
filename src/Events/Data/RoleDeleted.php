<?php

namespace Statamic\Events\Data;

class RoleDeleted extends Deleted
{
    /**
     * To sentence.
     *
     * @return string
     */
    public function toSentence()
    {
        return __('Role deleted.');
    }
}

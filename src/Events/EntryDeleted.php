<?php

namespace Statamic\Events;

class EntryDeleted extends Deleted
{
    public function commitMessage()
    {
        return __('Entry deleted');
    }
}

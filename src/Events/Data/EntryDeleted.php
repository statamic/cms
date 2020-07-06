<?php

namespace Statamic\Events\Data;

class EntryDeleted extends Deleted
{
    public function commitMessage()
    {
        return __('Entry deleted');
    }
}

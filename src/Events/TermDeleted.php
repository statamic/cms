<?php

namespace Statamic\Events;

class TermDeleted extends Deleted
{
    public function commitMessage()
    {
        return __('Term deleted');
    }
}

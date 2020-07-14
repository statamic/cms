<?php

namespace Statamic\Events\Data;

class TermDeleted extends Deleted
{
    public function commitMessage()
    {
        return __('Term deleted');
    }
}

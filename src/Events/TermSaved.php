<?php

namespace Statamic\Events;

class TermSaved extends Saved
{
    public function commitMessage()
    {
        return __('Term saved');
    }
}

<?php

namespace Statamic\Events\Data;

class TermSaved extends Saved
{
    public function commitMessage()
    {
        return __('Term saved');
    }
}

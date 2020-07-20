<?php

namespace Statamic\Events;

class EntrySaved extends Saved
{
    public function commitMessage()
    {
        return __('Entry saved');
    }
}

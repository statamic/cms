<?php

namespace Statamic\Events\Data;

class EntrySaved extends Saved
{
    public function commitMessage()
    {
        return __('Entry saved');
    }
}

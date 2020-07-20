<?php

namespace Statamic\Events;

class UserSaved extends Saved
{
    public $item;

    public function __construct($item)
    {
        $this->item = $item;
    }

    public function commitMessage()
    {
        return __('User saved');
    }
}

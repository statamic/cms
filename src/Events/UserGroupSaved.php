<?php

namespace Statamic\Events;

class UserGroupSaved extends Saved
{
    public $item;

    public function __construct($item)
    {
        $this->item = $item;
    }

    public function commitMessage()
    {
        return __('User group saved');
    }
}

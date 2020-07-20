<?php

namespace Statamic\Events;

class BlueprintSaved extends Saved
{
    public function commitMessage()
    {
        return __('Blueprint saved');
    }
}

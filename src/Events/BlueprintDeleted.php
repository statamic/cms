<?php

namespace Statamic\Events;

class BlueprintDeleted extends Deleted
{
    public function commitMessage()
    {
        return __('Blueprint deleted');
    }
}

<?php

namespace Statamic\Events\Data;

class BlueprintDeleted extends Deleted
{
    public function commitMessage()
    {
        return __('Blueprint deleted');
    }
}

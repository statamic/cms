<?php

namespace Statamic\Events\Data;

class BlueprintSaved extends Saved
{
    public function commitMessage()
    {
        return __('Blueprint saved');
    }
}

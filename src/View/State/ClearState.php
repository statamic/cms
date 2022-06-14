<?php

namespace Statamic\View\State;

class ClearState
{
    public function handle()
    {
        StateManager::resetState();
    }
}

<?php

namespace Statamic\Support\Traits;

use Statamic\Tags\Parameters;

trait ChecksDumpability
{
    protected function dumpingAllowed()
    {
        if (app()->hasDebugModeEnabled()) {
            return true;
        }

        if (isset($this->params) && $this->params instanceof Parameters && $this->params->get('force')) {
            return true;
        }

        return false;
    }
}

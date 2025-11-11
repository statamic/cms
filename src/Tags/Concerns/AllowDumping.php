<?php

namespace Statamic\Tags\Concerns;

use Statamic\Tags\Parameters;

trait AllowDumping
{
    private function allowDumping()
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

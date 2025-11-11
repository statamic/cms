<?php

namespace Statamic\Tags\Concerns;

trait AllowDumping
{
    private function allowDumping()
    {
        return app()->hasDebugModeEnabled() || $this->params->get('force');
    }
}

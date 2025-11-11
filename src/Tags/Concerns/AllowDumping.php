<?php

namespace Statamic\Tags\Concerns;

trait AllowDumping
{
    private function allowDumping()
    {
        return config('app.debug') || $this->params->get('force');
    }
}

<?php

namespace Statamic\CommandPalette\Concerns;

trait TracksRecent
{
    protected $trackRecent = true;

    public function trackRecent(bool $trackRecent = true): static
    {
        $this->trackRecent = $trackRecent;

        return $this;
    }
}

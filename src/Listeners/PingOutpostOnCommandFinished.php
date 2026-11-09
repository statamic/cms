<?php

namespace Statamic\Listeners;

use Illuminate\Console\Events\CommandFinished;
use Statamic\Licensing\Radio;

class PingOutpostOnCommandFinished
{
    public function __construct(private Radio $radio)
    {
    }

    public function handle(CommandFinished $event): void
    {
        if (! $this->radio->shouldPingCommand($event->command)) {
            return;
        }

        $this->radio->ping();
    }
}

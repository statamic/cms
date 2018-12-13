<?php

namespace Statamic\Console\Commands;

use Illuminate\Console\Command as ConsoleCommand;

class Command extends ConsoleCommand
{
    /**
     * Remove statamic grouping from signature for statamic please command.
     */
    public function removeStatamicGrouping()
    {
        $this->signature = str_replace('statamic:', '', $this->signature);

        $this->configureUsingFluentDefinition();
    }
}

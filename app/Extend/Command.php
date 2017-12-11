<?php

namespace Statamic\Extend;

use Statamic\Console\EnhancesCommands;
use Illuminate\Console\Command as LaravelCommand;

class Command extends LaravelCommand
{
    /**
     * Provides access to addon helper methods
     */
    use Extensible;

    /**
     * Provides various command enhancements
     */
    use EnhancesCommands;
}

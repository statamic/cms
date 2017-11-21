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

    /**
     * Create a new Command instance
     */
    public function __construct()
    {
        parent::__construct();

        $this->bootstrap();
        $this->init();
    }
}

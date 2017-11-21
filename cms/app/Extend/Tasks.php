<?php

namespace Statamic\Extend;

use Illuminate\Console\Scheduling\Schedule;

/**
 * Repeatable tasks via cron
 */
abstract class Tasks
{
    /**
     * Provides access to addon helper methods
     */
    use Extensible;

    /**
     * Create a new Tasks instance
     */
    public function __construct()
    {
        $this->bootstrap();
        $this->init();
    }

    /**
     * Define the task schedule
     *
     * @param \Illuminate\Console\Scheduling\Schedule $schedule
     */
    abstract public function schedule(Schedule $schedule);
}

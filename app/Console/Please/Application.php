<?php

namespace Statamic\Console\Please;

use Statamic\Console\Commands\Traits\RunsInPlease;
use Illuminate\Console\Application as ConsoleApplication;

class Application extends ConsoleApplication
{
    /**
     * Add a command, resolving through the application.
     *
     * @param  string  $command
     * @return \Symfony\Component\Console\Command\Command
     */
    public function resolve($command)
    {
        $command = $this->laravel->make($command);

        if (! in_array(RunsInPlease::class, class_uses($command))) {
            return;
        }

        $command->removeStatamicGrouping();

        return $this->add($command);
    }
}

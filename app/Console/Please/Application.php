<?php

namespace Statamic\Console\Please;

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
        if (! str_contains($command, 'Statamic\\')) {
            return;
        }

        $command = $this->laravel->make($command);

        if (method_exists($command, 'removeStatamicGrouping')) {
            $command->removeStatamicGrouping();
        }

        return $this->add($command);
    }
}

<?php

namespace Statamic\Console\Please;

use Illuminate\Console\Application as ConsoleApplication;
use Statamic\Console\RunsInPlease;
use Symfony\Component\Console\Exception\CommandNotFoundException;

class Application extends ConsoleApplication
{
    /**
     * Deferred artisan commands.
     *
     * @var array
     */
    protected $deferredCommands;

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
            $this->deferredCommands[] = $command;

            return;
        }

        $command->setRunningInPlease();
        $command->removeStatamicGrouping();
        $command->setHiddenInPlease();

        return $this->add($command);
    }

    /**
     * Finds a command by name or alias.  If doesn't exist, resolve deferred artisan commands and try again.
     *
     * @param  string  $name
     * @return \Illuminate\Console\Command
     *
     * @throws CommandNotFoundException
     */
    public function find($name)
    {
        try {
            return parent::find($name);
        } catch (CommandNotFoundException $exception) {
            $this->resolveDeferredCommands();
        }

        return parent::find($name);
    }

    /**
     * Resolve deferred commands.
     */
    protected function resolveDeferredCommands()
    {
        foreach ($this->deferredCommands as $command) {
            $this->add($command);
        }
    }
}

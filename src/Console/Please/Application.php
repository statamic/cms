<?php

namespace Statamic\Console\Please;

use Illuminate\Console\Application as ConsoleApplication;
use Statamic\Console\RunsInPlease;
use Symfony\Component\Console\Command\Command;
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
     * @param  Command|string  $command
     * @return \Symfony\Component\Console\Command\Command|void
     */
    public function resolve($command)
    {
        if (is_string($command)) {
            $command = $this->laravel->make($command);
        }

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
     *
     * @throws CommandNotFoundException
     */
    public function find(string $name): Command
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
    public function resolveDeferredCommands()
    {
        foreach ($this->deferredCommands as $command) {
            $this->add($command);
        }
    }

    public static function rebindKernel(): void
    {
        if (! class_exists('App\Console\Kernel')) {
            require_once __DIR__.'/app-kernel.php';
        }

        app()->singleton(
            \Illuminate\Contracts\Console\Kernel::class,
            \Statamic\Console\Please\Kernel::class
        );
    }
}

<?php

use Statamic\Facades\Blink;

class StarterKitPostInstall
{
    public $registerCommands = [
        StarterKitTestCommand::class,
    ];

    public function handle($console)
    {
        $console->call('statamic:test:starter-kit-command');
    }
}

class StarterKitTestCommand extends \Illuminate\Console\Command
{
    protected $name = 'statamic:test:starter-kit-command';

    public function handle()
    {
        Blink::put('post-install-hook-run', true);
    }
}

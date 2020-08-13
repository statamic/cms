<?php

namespace Statamic\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Foundation\Application;
use Statamic\Console\RunsInPlease;
use Statamic\Facades\Addon;
use Statamic\Statamic;

class DebugCommand extends Command
{
    use RunsInPlease;

    protected $signature = 'statamic:support:details';
    protected $description = 'Outpots details helpful for support requests.';

    public function handle()
    {
        $this->line('Statamic version: '.Statamic::version());
        $this->line('PHP version: '.phpversion());
        $this->line('Laravel version: '.Application::VERSION);
        $this->line('Statamic Pro: '.(Statamic::pro() === 1 ? 'True' : 'False'));
        $this->line('');
        $this->info('Installed Addons');

        foreach (Addon::all() as $addon) {
            $this->line($addon->name().' - '.$addon->version());
        }
    }
}

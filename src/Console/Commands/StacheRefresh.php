<?php

namespace Statamic\Console\Commands;

use Illuminate\Console\Command;
use Statamic\Console\RunsInPlease;
use Statamic\Facades\Stache;

use function Laravel\Prompts\spin;

class StacheRefresh extends Command
{
    use RunsInPlease;

    protected $signature = 'statamic:stache:refresh';
    protected $description = 'Clear and rebuild the "Stache" cache';

    public function handle()
    {
        spin(callback: fn () => Stache::clear(), message: 'Clearing the Stache...');
        spin(callback: fn () => Stache::warm(), message: 'Warming the Stache...');

        $this->components->info('You have trimmed and polished the Stache. It is handsome, warm, and ready.');
    }
}

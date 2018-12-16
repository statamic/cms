<?php

namespace Statamic\Console\Commands;

use Statamic\API\Stache;
use Illuminate\Console\Command;
use Statamic\Console\Commands\Traits\RunsInPlease;

class StacheRefresh extends Command
{
    use RunsInPlease;

    protected $signature = 'statamic:stache:refresh';
    protected $description = 'Clear and rebuild the "Stache" cache';

    public function handle()
    {
        Stache::refresh();

        $this->info('The Stache has been trimmed, regrown, and groomed.');
    }
}

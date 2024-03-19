<?php

namespace Statamic\Console\Commands;

use Illuminate\Console\Command;
use Statamic\Console\RunsInPlease;
use Statamic\Facades\Stache;
use Wilderborn\Partyline\Facade as Partyline;

class StacheRefresh extends Command
{
    use RunsInPlease;

    protected $signature = 'statamic:stache:refresh';
    protected $description = 'Clear and rebuild the "Stache" cache';

    public function handle()
    {
        Partyline::bind($this);

        $this->line('Please wait. This may take a while if you have a lot of content.');

        Stache::refresh();

        $this->info('You have trimmed and polished the Stache. It is handsome, warm, and ready.');
    }
}

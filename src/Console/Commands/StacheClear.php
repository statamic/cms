<?php

namespace Statamic\Console\Commands;

use Illuminate\Console\Command;
use Statamic\Console\RunsInPlease;
use Statamic\Facades\Stache;

class StacheClear extends Command
{
    use RunsInPlease;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'statamic:stache:clear';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clear the "Stache" cache';

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle()
    {
        Stache::clear();

        $this->info('You have trimmed the Stache. It looks dashing.');
    }
}

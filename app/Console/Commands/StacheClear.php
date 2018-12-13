<?php

namespace Statamic\Console\Commands;

use Statamic\API\Stache;
use Statamic\Console\Commands\Command;

class StacheClear extends Command
{
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

        $this->info('The Stache has been trimmed.');
    }
}

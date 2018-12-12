<?php

namespace Statamic\Console\Commands;

use Statamic\API\Stache;
use Illuminate\Console\Command;

class StacheClear extends Command
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'statamic:stache:clear';

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

<?php

namespace Statamic\Console\Commands;

use Illuminate\Console\Command;
use Statamic\StaticCaching\Cacher as StaticCacher;

class StaticClear extends Command
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'statamic:static:clear';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clear the static page cache';

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle()
    {
        app(StaticCacher::class)->flush();

        $this->info('The static page cache has been cleared.');
    }
}

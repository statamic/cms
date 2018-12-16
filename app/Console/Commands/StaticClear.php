<?php

namespace Statamic\Console\Commands;

use Illuminate\Console\Command;
use Statamic\Console\RunsInPlease;
use Statamic\StaticCaching\Cacher as StaticCacher;

class StaticClear extends Command
{
    use RunsInPlease;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'statamic:static:clear';

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

        $this->info('Your static page cache is now so very, very empty.');
    }
}

<?php

namespace Statamic\Console\Commands;

use Illuminate\Console\Command;
use Statamic\Console\RunsInPlease;
use Statamic\Facades\StaticCache;

use function Laravel\Prompts\spin;

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
        if (! config('statamic.static_caching.strategy')) {
            $this->components->error('Static caching is not enabled.');

            return 0;
        }

        spin(callback: fn () => StaticCache::flush(), message: 'Clearing the static page cache...');

        $this->components->info('Your static page cache is now so very, very empty.');
    }
}

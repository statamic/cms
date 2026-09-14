<?php

namespace Statamic\Console\Commands;

use Illuminate\Console\Command;
use Statamic\Console\Commands\Concerns\HasStacheExcludes;
use Statamic\Console\RunsInPlease;
use Statamic\Facades\Stache;

class StacheClear extends Command
{
    use HasStacheExcludes, RunsInPlease;

    protected $signature = 'statamic:stache:clear {--exclude= : Comma-separated list of store keys to exclude}';

    protected $description = 'Clear the "Stache" cache';

    public function handle()
    {
        $this->addExcludes($this->option('exclude'));

        Stache::clear();

        $this->components->info('You have trimmed the Stache. It looks dashing.');
    }
}

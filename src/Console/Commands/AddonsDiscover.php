<?php

namespace Statamic\Console\Commands;

use Illuminate\Console\Command;
use Statamic\Addons\Manifest;
use Statamic\Console\RunsInPlease;

class AddonsDiscover extends Command
{
    use RunsInPlease;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'statamic:addons:discover';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Rebuild the cached addon package manifest';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle(Manifest $manifest)
    {
        $this->newLine();
        $manifest->build();

        $this->components->info('Discovering addons.');

        foreach (array_keys($manifest->manifest) as $package) {
            $this->components->task("Discovered Addon: <info>{$package}</info>");
        }
    }
}

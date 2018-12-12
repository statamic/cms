<?php

namespace Statamic\Console\Commands;

use Illuminate\Console\Command;
use Statamic\Extend\Management\Manifest;

class AddonsDiscover extends Command
{
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
        $manifest->build();

        foreach (array_keys($manifest->manifest) as $package) {
            $this->line("<info>Discovered Addon:</info> {$package}");
        }

        $this->info('Addon manifest generated successfully.');

    }
}

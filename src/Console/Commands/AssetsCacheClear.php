<?php

namespace Statamic\Console\Commands;

use Illuminate\Console\Command;
use Statamic\Assets\Asset;
use Statamic\Assets\AssetContainerContents;
use Statamic\Console\RunsInPlease;

use function Laravel\Prompts\spin;

class AssetsCacheClear extends Command
{
    use RunsInPlease;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'statamic:assets:clear-cache';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clear the asset meta and folder cache';

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle()
    {
        if (! config()->has('cache.stores.asset_meta') && ! config()->has('cache.stores.asset_container_contents')) {
            $this->components->error('You do not have any custom asset cache stores.');

            return 0;
        }

        if (config()->has('cache.stores.asset_meta')) {
            spin(callback: fn () => Asset::make()->cacheStore()->flush(), message: 'Clearing the asset meta cache...');

            $this->components->info('Your asset meta cache is now so very, very empty.');
        }

        if (config()->has('cache.stores.asset_container_contents')) {
            spin(callback: fn () => (new AssetContainerContents)->cacheStore()->flush(), message: 'Clearing the asset folder cache...');

            $this->components->info('Your asset folder cache is now so very, very empty.');
        }
    }
}

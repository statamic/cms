<?php

namespace Statamic\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Str;
use Statamic\Console\RunsInPlease;
use Statamic\Facades\Asset;
use Statamic\Facades\AssetContainer;

use function Laravel\Prompts\progress;

class AssetsMeta extends Command
{
    use RunsInPlease;

    protected $signature = 'statamic:assets:meta { container? : Handle of a container }';

    protected $description = 'Generate asset metadata files';

    public function handle()
    {
        $assets = $this->getAssets();

        if ($assets->isEmpty()) {
            return $this->components->warn("There's no metadata to generate. You don't have any assets.");
        }

        progress(
            label: 'Generating asset metadata...',
            steps: $assets,
            callback: function ($asset, $progress) {
                $asset->hydrate();
                $asset->save();
                $progress->advance();
            },
            hint: 'This may take a while if you have a lot of assets.'
        );

        $this->components->info("Generated metadata for {$assets->count()} ".Str::plural('asset', $assets->count()).'.');
    }

    /**
     * @return \Statamic\Assets\AssetCollection
     */
    protected function getAssets()
    {
        if (! $container = $this->argument('container')) {
            return Asset::all();
        }

        if (! $container = AssetContainer::find($container)) {
            throw new \InvalidArgumentException('Invalid container');
        }

        return $container->assets();
    }
}

<?php

namespace Statamic\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Contracts\Bus\Dispatcher;
use Illuminate\Support\Facades\Log;
use Statamic\Console\RunsInPlease;
use Statamic\Facades\AssetContainer;
use Statamic\Jobs\GeneratePresetImageManipulation;
use Statamic\Support\Arr;

use function Laravel\Prompts\error;
use function Laravel\Prompts\info;
use function Laravel\Prompts\note;
use function Laravel\Prompts\progress;

class AssetsGeneratePresets extends Command
{
    use RunsInPlease;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'statamic:assets:generate-presets
        {--queue : Queue the image generation.}
        {--excluded-containers= : Comma separated list of container handles to exclude.}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate asset preset manipulations';

    /**
     * @var \Statamic\Assets\AssetCollection
     */
    protected $imageAssets;

    /**
     * @var bool
     */
    protected $shouldQueue = false;

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->shouldQueue = $this->option('queue');

        if ($this->shouldQueue && config('queue.default') === 'sync') {
            error('The queue connection is set to "sync". Queueing will be disabled.');
            $this->shouldQueue = false;
        }

        $excludedContainers = $this->option('excluded-containers');

        if ($excludedContainers) {
            $excludedContainers = explode(',', $excludedContainers);
        }

        AssetContainer::all()->filter(function ($container) use ($excludedContainers) {
            return ! in_array($container->handle(), $excludedContainers ?? []);
        })->sortBy('title')->each(function ($container) {
            note('Generating presets for <comment>'.$container->title().'</comment>...');
            $this->generatePresets($container);
            $this->newLine();
        });
    }

    /**
     * Generate presets for a container.
     *
     * @param  \Statamic\Contracts\Assets\AssetContainer  $container
     * @return void
     */
    private function generatePresets($container)
    {
        $assets = $container->assets()->filter->isImage();
        $counts = [];

        // The amount of extra cp presets for each asset. The amount will
        // be consistent across assets, but just not the preset names.
        $cpPresets = config('statamic.cp.enabled') ? 1 : 0;

        $steps = (count($container->warmPresets()) + $cpPresets) * count($assets);

        if ($steps > 0) {
            $progress = progress(
                label: $this->shouldQueue ? 'Queueing...' : 'Generating...',
                steps: $steps
            );

            $progress->start();

            foreach ($assets as $asset) {
                foreach ($asset->warmPresets() as $preset) {
                    $counts[$preset] = ($counts[$preset] ?? 0) + 1;
                    $progress->label("Generating $preset for {$asset->basename()}...");

                    $dispatchMethod = $this->shouldQueue
                        ? 'dispatch'
                        : (method_exists(Dispatcher::class, 'dispatchSync') ? 'dispatchSync' : 'dispatchNow');

                    try {
                        GeneratePresetImageManipulation::$dispatchMethod($asset, $preset);
                    } catch (\Exception $e) {
                        Log::debug($e);
                        $counts['errors'] = ($counts['errors'] ?? 0) + 1;
                    }

                    $progress->advance();
                }
            }

            $progress->finish();
        }

        $verb = $this->shouldQueue ? 'queued' : 'generated';
        info(sprintf("<info>[✔]</info> %s images $verb for %s assets.", $steps, count($assets)));

        if (property_exists($this, 'components')) {
            $errors = Arr::pull($counts, 'errors');
            collect($counts)
                ->put('errors', $errors)
                ->each(function ($count, $preset) {
                    $preset = $preset === 'errors' ? '<fg=red>errors</>' : $preset;
                    $this->components->twoColumnDetail($preset, $count);
                });
        }

        $this->output->newLine();
    }
}

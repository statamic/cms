<?php

namespace Statamic\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Contracts\Bus\Dispatcher;
use Illuminate\Support\Facades\Log;
use Statamic\Console\RunsInPlease;
use Statamic\Facades\AssetContainer;
use Statamic\Jobs\GeneratePresetImageManipulation;
use Statamic\Support\Arr;

class AssetsGeneratePresets extends Command
{
    use RunsInPlease;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'statamic:assets:generate-presets {--queue : Queue the image generation.}';

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
            $this->error('The queue connection is set to "sync". Queueing will be disabled.');
            $this->shouldQueue = false;
        }

        AssetContainer::all()->sortBy('title')->each(function ($container) {
            $this->line('Generating presets for <comment>'.$container->title().'</comment>...');
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
        $bar = $this->output->createProgressBar($steps);

        foreach ($assets as $asset) {
            $verb = $this->shouldQueue ? 'Queueing' : 'Generating';
            $bar->setFormat("[%current%/%max%] $verb %filename% <comment>%preset%</comment>... ");

            foreach ($asset->warmPresets() as $preset) {
                $counts[$preset] = ($counts[$preset] ?? 0) + 1;
                $bar->setMessage($preset, 'preset');
                $bar->setMessage($asset->basename(), 'filename');
                $bar->display();

                $dispatchMethod = $this->shouldQueue
                    ? 'dispatch'
                    : (method_exists(Dispatcher::class, 'dispatchSync') ? 'dispatchSync' : 'dispatchNow');

                try {
                    GeneratePresetImageManipulation::$dispatchMethod($asset, $preset);
                } catch (\Exception $e) {
                    Log::debug($e);
                    $counts['errors'] = ($counts['errors'] ?? 0) + 1;
                }

                $bar->advance();
            }
        }

        $verb = $this->shouldQueue ? 'queued' : 'generated';
        $bar->setFormat(sprintf("<info>[✔]</info> %s images $verb for %s assets.", $steps, count($assets)));
        $bar->finish();
        $this->newLine(2);

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

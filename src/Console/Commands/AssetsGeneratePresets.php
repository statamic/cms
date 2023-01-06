<?php

namespace Statamic\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Contracts\Bus\Dispatcher;
use Statamic\Console\RunsInPlease;
use Statamic\Facades\AssetContainer;
use Statamic\Facades\Image;
use Statamic\Jobs\GeneratePresetImageManipulation;

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

        AssetContainer::all()->each(function ($container) {
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

        $cpPresets = config('statamic.cp.enabled') ? array_keys(Image::getCpImageManipulationPresets()) : [];

        $presets = array_merge($container->warmPresets(), $cpPresets);

        foreach ($presets as $preset) {
            $bar = $this->output->createProgressBar($assets->count());

            $verb = $this->shouldQueue ? 'Queueing' : 'Generating';
            $bar->setFormat("[%current%/%max%] $verb <comment>$preset</comment>... %filename%");

            foreach ($assets as $asset) {
                $bar->setMessage($asset->basename(), 'filename');

                $dispatchMethod = $this->shouldQueue
                    ? 'dispatch'
                    : (method_exists(Dispatcher::class, 'dispatchSync') ? 'dispatchSync' : 'dispatchNow');

                try {
                    GeneratePresetImageManipulation::$dispatchMethod($asset, $preset);
                } catch (\Exception $e) {
                    $this->error($asset.' '.$e->getMessage());
                }

                $bar->advance();
            }

            $verb = $this->shouldQueue ? 'queued' : 'generated';
            $bar->setFormat("<info>[✓] Images $verb for <comment>$preset</comment>.</info>");

            $bar->finish();

            $this->output->newLine();
        }
    }
}

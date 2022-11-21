<?php

namespace Statamic\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Contracts\Bus\Dispatcher;
use Statamic\Console\RunsInPlease;
use Statamic\Facades\Asset;
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

        $this->imageAssets = Asset::all()->filter(function ($asset) {
            return $asset->isImage();
        });

        $this->generateUserPresets();

        $this->generateCpThumbnails();
    }

    /**
     * Generate user provided presets.
     *
     * @return void
     */
    protected function generateUserPresets()
    {
        $presets = Image::userManipulationPresets();

        if (empty($presets)) {
            return $this->line('<fg=red>[✗]</> No user defined presets.');
        }

        $this->generatePresets($presets);
    }

    /**
     * Generate thumbnails required by the control panel.
     *
     * @return void
     */
    private function generateCpThumbnails()
    {
        if (! config('statamic.cp.enabled')) {
            return;
        }

        $this->generatePresets(Image::getCpImageManipulationPresets());
    }

    /**
     * Generate supplied presets.
     *
     * @param  array  $presets
     * @return void
     */
    private function generatePresets($presets)
    {
        foreach ($presets as $preset => $params) {
            $bar = $this->output->createProgressBar($this->imageAssets->count());

            $verb = $this->shouldQueue ? 'Queueing' : 'Generating';
            $bar->setFormat("[%current%/%max%] $verb <comment>$preset</comment>... %filename%");

            foreach ($this->imageAssets as $asset) {
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

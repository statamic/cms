<?php

namespace Statamic\Console\Commands;

use Illuminate\Console\Command;
use Statamic\Console\RunsInPlease;
use Statamic\Facades\Asset;
use Statamic\Facades\Config;
use Statamic\Facades\Image;
use Statamic\Imaging\PresetGenerator;

class AssetsGeneratePresets extends Command
{
    use RunsInPlease;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'statamic:assets:generate-presets';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate asset preset manipulations.';

    /**
     * @var PresetGenerator
     */
    protected $generator;

    /**
     * @var \Statamic\Assets\AssetCollection
     */
    protected $imageAssets;

    public function __construct(PresetGenerator $generator)
    {
        $this->generator = $generator;
        parent::__construct();
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
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
     * @param array $presets
     * @return void
     */
    private function generatePresets($presets)
    {
        foreach ($presets as $preset => $params) {
            $bar = $this->output->createProgressBar($this->imageAssets->count());
            $bar->setFormat("[%current%/%max%] Generating <comment>$preset</comment>... %filename%");

            foreach ($this->imageAssets as $asset) {
                $bar->setMessage($asset->basename(), 'filename');
                $this->generator->generate($asset, $preset);
                $bar->advance();
            }

            $bar->setFormat("<info>[✓] Images generated for <comment>$preset</comment>.</info>");
            $bar->finish();

            $this->output->newLine();
        }
    }
}

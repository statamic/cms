<?php

namespace Statamic\Imaging;

use Statamic\Contracts\Assets\Asset;

class PresetGenerator
{
    /**
     * @var ImageGenerator
     */
    private $generator;

    /**
     * @param  ImageGenerator  $generator
     */
    public function __construct(ImageGenerator $generator)
    {
        $this->generator = $generator;
    }

    /**
     * Generate presets for an asset.
     *
     * @param  Asset  $asset  The asset to use for generating presets
     * @param  string|null  $preset  An optional name for generating a specific preset.
     *                               If left blank, all asset presets will be generated.
     */
    public function generate(Asset $asset, $preset = null)
    {
        $presets = $preset ? [$preset] : $asset->warmPresets();

        collect($presets)->each(function ($preset) use ($asset) {
            $this->generator->generateByAsset($asset, ['p' => $preset]);
        });
    }
}

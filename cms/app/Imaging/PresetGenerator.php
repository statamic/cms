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
     * @var array
     */
    private $presets;

    /**
     * @param ImageGenerator $generator
     * @param array $presets
     */
    public function __construct(ImageGenerator $generator, array $presets)
    {
        $this->generator = $generator;
        $this->presets = $presets;
    }

    /**
     * Generate presets for an asset
     *
     * @param Asset $asset         The asset to use for generating presets
     * @param string|null $preset  An optional name for generating a specific preset.
     *                             If left blank, all the presets will be generated.
     */
    public function generate(Asset $asset, $preset = null)
    {
        $presets = ($preset)
            ? [$preset => []]
            : $this->presets;

        foreach ($presets as $name => $params) {
            $this->generator->generateByAsset($asset, ['p' => $name]);
        }
    }
}

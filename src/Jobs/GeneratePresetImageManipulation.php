<?php

namespace Statamic\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Statamic\Contracts\Assets\Asset;
use Statamic\Imaging\PresetGenerator;

class GeneratePresetImageManipulation implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable;

    public $asset;
    public $preset;

    public function __construct(Asset $asset, $preset)
    {
        $this->asset = $asset;
        $this->preset = $preset;
    }

    public function handle(PresetGenerator $generator)
    {
        $generator->generate($this->asset, $this->preset);
    }
}

<?php

namespace Tests\Assets;

use Illuminate\Contracts\Bus\Dispatcher;
use Mockery;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Assets\Asset;
use Statamic\Events\AssetReuploaded;
use Statamic\Events\AssetUploaded;
use Statamic\Imaging\PresetGenerator;
use Statamic\Listeners\GeneratePresetImageManipulations;
use Tests\TestCase;

class GeneratePresetImageManipulationsOnUpload extends TestCase
{
    #[Test]
    public function it_subscribes()
    {
        $events = Mockery::mock(Dispatcher::class);
        $events->shouldReceive('listen')->with(AssetUploaded::class, GeneratePresetImageManipulations::class.'@handle')->once();
        $events->shouldReceive('listen')->with(AssetReuploaded::class, GeneratePresetImageManipulations::class.'@handle')->once();

        $generator = Mockery::mock(PresetGenerator::class);

        (new GeneratePresetImageManipulations($generator))->subscribe($events);
    }

    #[Test]
    #[DataProvider('presetProvider')]
    public function presets_are_generated_for_images($event, $basename, $shouldGenerate)
    {
        $generator = Mockery::mock(PresetGenerator::class);
        $asset = (new Asset)->path($basename);

        if ($shouldGenerate) {
            $generator->shouldReceive('generate')->once()->with($asset);
        } else {
            $generator->shouldReceive('generate')->never();
        }

        $listener = new GeneratePresetImageManipulations($generator);

        $listener->handle(new $event($asset, $basename));
    }

    public static function presetProvider()
    {
        return [
            [AssetUploaded::class, 'foo.jpg', true],
            [AssetUploaded::class, 'foo.svg', false],
            [AssetUploaded::class, 'foo.txt', false],

            [AssetReuploaded::class, 'foo.jpg', true],
            [AssetReuploaded::class, 'foo.svg', false],
            [AssetReuploaded::class, 'foo.txt', false],
        ];
    }
}

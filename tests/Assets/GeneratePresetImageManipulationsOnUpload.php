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
    public function presets_are_generated_for_images($event, $extension, $shouldGenerate)
    {
        $generator = Mockery::mock(PresetGenerator::class);
        $asset = (new Asset)->path('foo.'.$extension);

        if ($shouldGenerate) {
            $generator->shouldReceive('generate')->once()->with($asset);
        } else {
            $generator->shouldReceive('generate')->never();
        }

        $listener = new GeneratePresetImageManipulations($generator);

        $listener->handle(new $event($asset));
    }

    public static function presetProvider()
    {
        return [
            [AssetUploaded::class, 'jpg', true],
            [AssetUploaded::class, 'svg', false],
            [AssetUploaded::class, 'txt', false],

            [AssetReuploaded::class, 'jpg', true],
            [AssetReuploaded::class, 'svg', false],
            [AssetReuploaded::class, 'txt', false],
        ];
    }
}

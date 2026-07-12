<?php

namespace Tests\Imaging;

use Mockery;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Contracts\Assets\Asset;
use Statamic\Imaging\ImageGenerator;
use Statamic\Imaging\PresetGenerator;
use Tests\TestCase;

class PresetGeneratorTest extends TestCase
{
    #[Test]
    public function it_generates_all_presets_for_an_asset()
    {
        $asset = Mockery::mock(Asset::class);
        $asset->shouldReceive('warmPresets')->once()->andReturn(['one', 'two']);

        $imageGenerator = Mockery::mock(ImageGenerator::class);
        $imageGenerator->shouldReceive('generateByAsset')->with($asset, ['p' => 'one'])->once();
        $imageGenerator->shouldReceive('generateByAsset')->with($asset, ['p' => 'two'])->once();

        $generator = new PresetGenerator($imageGenerator);

        $generator->generate($asset);
    }

    #[Test]
    public function it_generates_a_specific_asset_preset()
    {
        $asset = Mockery::mock(Asset::class);
        $asset->shouldReceive('warmPresets')->never();

        $imageGenerator = Mockery::mock(ImageGenerator::class);
        $imageGenerator->shouldReceive('generateByAsset')->with($asset, ['p' => 'whatever'])->once();

        $generator = new PresetGenerator($imageGenerator);

        $generator->generate($asset, 'whatever');
    }
}

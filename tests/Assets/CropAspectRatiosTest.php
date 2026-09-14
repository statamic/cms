<?php

namespace Tests\Assets;

use Illuminate\Support\Str;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Assets\CropAspectRatios;
use Tests\TestCase;

class CropAspectRatiosTest extends TestCase
{
    #[Test]
    public function it_returns_default_ratios_from_config()
    {
        config()->set('statamic.assets.crop_aspect_ratios', [
            '16:9',
            '4:3',
        ]);

        $this->assertSame([
            ['label' => '16:9', 'value' => 16 / 9],
            ['label' => '4:3', 'value' => 4 / 3],
        ], CropAspectRatios::all());
    }

    #[Test]
    public function it_supports_custom_labels_and_fractional_ratio_strings()
    {
        config()->set('statamic.assets.crop_aspect_ratios', [
            ['label' => 'US Letter', 'ratio' => '8.5:11'],
            ['label' => 'Golden', 'ratio' => 1.618],
        ]);

        $this->assertSame([
            ['label' => 'US Letter', 'value' => 8.5 / 11],
            ['label' => 'Golden', 'value' => 1.618],
        ], CropAspectRatios::all());
    }

    #[Test]
    public function it_translates_labels()
    {
        app('translator')->addLines([
            'crop.wide' => 'Wide Screen',
        ], 'en');

        config()->set('statamic.assets.crop_aspect_ratios', [
            ['label' => 'crop.wide', 'ratio' => '16:9'],
            ['label' => 'Square', 'ratio' => '1:1'],
        ]);

        $this->assertSame([
            ['label' => 'Wide Screen', 'value' => 16 / 9],
            ['label' => 'Square', 'value' => 1.0],
        ], CropAspectRatios::all());
    }

    #[Test]
    public function it_accepts_numeric_and_stringable_labels()
    {
        config()->set('statamic.assets.crop_aspect_ratios', [
            ['label' => 123, 'ratio' => '16:9'],
            ['label' => Str::of('Stringable'), 'ratio' => '4:3'],
        ]);

        $this->assertSame([
            ['label' => '123', 'value' => 16 / 9],
            ['label' => 'Stringable', 'value' => 4 / 3],
        ], CropAspectRatios::all());
    }

    #[Test]
    public function it_skips_invalid_entries()
    {
        config()->set('statamic.assets.crop_aspect_ratios', [
            '16:9',
            'bad-value',
            ['label' => 'Zero height', 'ratio' => '4:0'],
            ['label' => 'Missing ratio'],
            ['ratio' => '4:3'],
            ['label' => 'Valid custom', 'ratio' => '5:4'],
        ]);

        $this->assertSame([
            ['label' => '16:9', 'value' => 16 / 9],
            ['label' => 'Valid custom', 'value' => 5 / 4],
        ], CropAspectRatios::all());
    }

    #[Test]
    public function it_returns_an_empty_array_when_config_is_empty()
    {
        config()->set('statamic.assets.crop_aspect_ratios', []);

        $this->assertSame([], CropAspectRatios::all());
    }
}

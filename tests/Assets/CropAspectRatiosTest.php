<?php

namespace Tests\Assets;

use PHPUnit\Framework\Attributes\Test;
use Statamic\Assets\CropAspectRatios;
use Tests\TestCase;

class CropAspectRatiosTest extends TestCase
{
    #[Test]
    public function it_returns_default_ratios_from_config()
    {
        config()->set('statamic.assets.image_cropping.aspect_ratios', [
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
        config()->set('statamic.assets.image_cropping.aspect_ratios', [
            ['label' => 'US Letter', 'ratio' => '8.5:11'],
            ['label' => 'Golden', 'ratio' => 1.618],
        ]);

        $this->assertSame([
            ['label' => 'US Letter', 'value' => 8.5 / 11],
            ['label' => 'Golden', 'value' => 1.618],
        ], CropAspectRatios::all());
    }

    #[Test]
    public function it_supports_keyed_ratio_entries()
    {
        config()->set('statamic.assets.image_cropping.aspect_ratios', [
            'Portrait' => '9:16',
            'A4' => '210:297',
        ]);

        $this->assertSame([
            ['label' => 'Portrait', 'value' => 9 / 16],
            ['label' => 'A4', 'value' => 210 / 297],
        ], CropAspectRatios::all());
    }

    #[Test]
    public function it_skips_invalid_entries()
    {
        config()->set('statamic.assets.image_cropping.aspect_ratios', [
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
        config()->set('statamic.assets.image_cropping.aspect_ratios', []);

        $this->assertSame([], CropAspectRatios::all());
    }
}

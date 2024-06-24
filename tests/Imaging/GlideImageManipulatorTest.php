<?php

namespace Tests\Imaging;

use Mockery;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Assets\Asset;
use Statamic\Contracts\Imaging\UrlBuilder;
use Statamic\Imaging\GlideImageManipulator;
use Statamic\Support\Arr;
use Tests\TestCase;

class GlideImageManipulatorTest extends TestCase
{
    /**
     * @var GlideImageManipulator
     */
    protected $man;

    /**
     * @var \Mockery\MockInterface
     */
    protected $builder;

    public function setUp(): void
    {
        parent::setUp();

        $this->man = new GlideImageManipulator(
            Mockery::mock(UrlBuilder::class)
        );
    }

    #[Test]
    #[DataProvider('paramProvider')]
    public function adds_standard_api_params($param)
    {
        $this->man->setParam($param, 'value');
        $this->assertArrayHasKey($param, $this->man->getParams());
    }

    #[Test]
    #[DataProvider('paramProvider')]
    public function adds_standard_api_params_using_magic_method($param)
    {
        $this->man->$param('value');
        $this->assertArrayHasKey($param, $this->man->getParams());
    }

    public static function paramProvider()
    {
        return [
            'or' => ['or'],
            'crop' => ['crop'],
            'w' => ['w'],
            'h' => ['h'],
            'fit' => ['fit'],
            'dpr' => ['dpr'],
            'bri' => ['bri'],
            'con' => ['con'],
            'gam' => ['gam'],
            'sharp' => ['sharp'],
            'blur' => ['blur'],
            'pixel' => ['pixel'],
            'filt' => ['filt'],
            'mark' => ['mark'],
            'markh' => ['markh'],
            'markw' => ['markw'],
            'markx' => ['markx'],
            'marky' => ['marky'],
            'markpad' => ['markpad'],
            'markpos' => ['markpos'],
            'markfit' => ['markfit'],
            'markalpha' => ['markalpha'],
            'bg' => ['bg'],
            'border' => ['border'],
            'q' => ['q'],
            'fm' => ['fm'],
            'p' => ['p'],
        ];
    }

    #[Test]
    public function cannot_add_invalid_glide_param()
    {
        $this->expectException('Exception');
        $this->expectExceptionMessage('Glide URL parameter [foo] does not exist.');
        $this->man->setParam('foo', 'bar');
    }

    #[Test]
    public function cannot_add_invalid_glide_param_using_magic_method()
    {
        $this->expectException('Exception');
        $this->expectExceptionMessage('Glide URL parameter [foo] does not exist.');
        $this->man->foo('bar');
    }

    #[Test]
    #[DataProvider('aliasProvider')]
    public function testAddsParamsUsingAliases($alias, $value, $expected)
    {
        $this->man->$alias($value);
        $this->assertArraySubset($expected, $this->man->getParams());
    }

    public static function aliasProvider()
    {
        return [
            'width' => ['width', 10, ['w' => 10]],
            'height' => ['height', 10, ['h' => 10]],
            'square' => ['square', 50, ['w' => 50, 'h' => 50]],
            'orient' => ['orient', 90, ['or' => 90]],
            'brightness' => ['brightness', 50, ['bri' => 50]],
            'contrast' => ['contrast', 50, ['con' => 50]],
            'gamma' => ['gamma', 50, ['gam' => 50]],
            'sharpen' => ['sharpen', 50, ['sharp' => 50]],
            'pixelate' => ['pixelate', 50, ['pixel' => 50]],
            'filter' => ['filter', 'sepia', ['filt' => 'sepia']],
            'quality' => ['quality', 50, ['q' => 50]],
        ];
    }

    #[Test]
    public function focal_crop_uses_asset_value()
    {
        $asset = $this->mock(Asset::class);
        $asset->shouldReceive('get')->with('focus')->andReturn('60-40');
        $this->man->item($asset);
        $this->man->fit('crop_focal');

        $this->assertArrayHasKey('fit', $this->man->getParams());
        $this->assertEquals('crop-60-40', Arr::get($this->man->getParams(), 'fit'));
    }

    #[Test]
    public function focal_crop_just_uses_crop_if_no_value_exists()
    {
        $asset = $this->mock(Asset::class);
        $asset->shouldReceive('get')->with('focus')->andReturnNull();
        $this->man->item($asset);
        $this->man->fit('crop_focal');

        $this->assertArrayHasKey('fit', $this->man->getParams());
        $this->assertEquals('crop', Arr::get($this->man->getParams(), 'fit'));
    }
}

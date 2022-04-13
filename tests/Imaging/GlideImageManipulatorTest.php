<?php

namespace Tests\Imaging;

use Mockery;
use Statamic\Assets\Asset;
use Statamic\Contracts\Imaging\UrlBuilder;
use Statamic\Imaging\GlideImageManipulator;
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

    /**
     * @test
     * @dataProvider paramProvider
     */
    public function testAddsParams($param)
    {
        $this->man->setParam($param, 'value');
        $this->assertArrayHasKey($param, $this->man->getParams());
    }

    public function paramProvider()
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

    public function testCannotAddNonGlideParam()
    {
        $this->expectException('Exception');
        $this->expectExceptionMessage('Glide URL parameter [foo] does not exist.');
        $this->man->setParam('foo', 'bar');
    }

    public function testCannotAddNonGlideParamUsingAlias()
    {
        $this->expectException('Exception');
        $this->expectExceptionMessage('Glide URL parameter [foo] does not exist.');
        $this->man->foo('bar');
    }

    public function testAddsParamsUsingAliases()
    {
        $this->man->width(10);
        $this->assertArraySubset(['w' => 10], $this->man->getParams());

        $this->man->height(10);
        $this->assertArraySubset(['h' => 10], $this->man->getParams());

        $this->man->square(50);
        $this->assertArraySubset(['w' => 50, 'h' => 50], $this->man->getParams());

        $this->man->orient('landscape');
        $this->assertArraySubset(['or' => 'landscape'], $this->man->getParams());

        $this->man->brightness(50);
        $this->assertArraySubset(['bri' => 50], $this->man->getParams());

        $this->man->contrast(50);
        $this->assertArraySubset(['con' => 50], $this->man->getParams());

        $this->man->gamma(50);
        $this->assertArraySubset(['gam' => 50], $this->man->getParams());

        $this->man->sharpen(50);
        $this->assertArraySubset(['sharp' => 50], $this->man->getParams());

        $this->man->pixelate(50);
        $this->assertArraySubset(['pixel' => 50], $this->man->getParams());

        $this->man->filter(50);
        $this->assertArraySubset(['filt' => 50], $this->man->getParams());

        $this->man->quality(50);
        $this->assertArraySubset(['q' => 50], $this->man->getParams());
    }

    public function testFocalCropUsesAssetValue()
    {
        $asset = $this->mock(Asset::class);
        $asset->shouldReceive('get')->with('focus')->andReturn('60-40');
        $this->man->item($asset);
        $this->man->fit('crop_focal');

        $this->assertArrayHasKey('fit', $this->man->getParams());
        $this->assertEquals('crop-60-40', array_get($this->man->getParams(), 'fit'));
    }

    public function testFocalCropJustUsesCropIfNoValueExists()
    {
        $asset = $this->mock(Asset::class);
        $asset->shouldReceive('get')->with('focus')->andReturnNull();
        $this->man->item($asset);
        $this->man->fit('crop_focal');

        $this->assertArrayHasKey('fit', $this->man->getParams());
        $this->assertEquals('crop', array_get($this->man->getParams(), 'fit'));
    }
}

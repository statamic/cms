<?php

namespace Tests\Imaging\Manipulators;

use Facades\Statamic\Imaging\Attributes;
use Mockery;
use Mockery\Mock;
use Statamic\Assets\Asset;
use Statamic\Assets\AssetContainer;
use Statamic\Exceptions\AssetNotFoundException;
use Statamic\Facades\Asset as Assets;
use Statamic\Imaging\Manipulators\Glide\ImageGenerator;
use Statamic\Imaging\Manipulators\GlideManipulator;
use Tests\TestCase;

class GlideManipulatorTest extends TestCase
{
    private GlideManipulator $manipulator;
    private ImageGenerator|Mock $generator;

    public function setUp(): void
    {
        parent::setUp();

        $this->generator = Mockery::mock(ImageGenerator::class);
        $this->manipulator = $this->manipulator();
        $this->manipulator->setGenerator($this->generator);
    }

    private function manipulator($config = [], $generator = null)
    {
        $manipulator = new GlideManipulator(array_merge([
            'cache' => public_path('img'),
            'url' => 'img',
        ], $config));

        if ($generator) {
            $manipulator->setGenerator($generator);
        }

        return $manipulator;
    }

    /**
     * @test
     *
     * @dataProvider libraryProvider
     */
    public function it_uses_library_from_config($config, $expected)
    {
        $this->assertEquals($expected, $this->manipulator($config)->getServer()->getApi()->getImageManager()->config['driver']);
    }

    public static function libraryProvider()
    {
        return [
            'default' => [[], 'gd'],
            'gd' => [['library' => 'gd'], 'gd'],
            'imagick' => [['library' => 'imagick'], 'imagick'],
        ];
    }

    /** @test */
    public function it_gets_cache_disk()
    {
        $this->assertEquals(public_path('images/foo.jpg'), $this->manipulator(['cache' => public_path('images')])->getCacheDisk()->path('foo.jpg'));
    }

    /** @test */
    public function it_gets_custom_cache_disk()
    {
        config(['filesystems.disks.customdisk' => [
            'driver' => 'local',
            'root' => public_path('custom-location'),
        ]]);
        $this->assertEquals(public_path('custom-location/foo.jpg'), $this->manipulator(['cache' => 'customdisk'])->getCacheDisk()->path('foo.jpg'));
    }

    /** @test */
    public function it_throws_exception_when_cache_is_undefined()
    {
        // We throw an exception rather than assuming a default location to prevent accidentally overwriting files.

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Glide cache is not defined.');

        $this->manipulator(['cache' => null])->getCacheDisk();
    }

    /** @test */
    public function it_gets_url_for_path()
    {
        $this->generator->shouldReceive('generateByPath')
            ->with('/foo bar.jpg', ['w' => 100])
            ->andReturn('the-url-for-path');

        $url = $this->manipulator->setSource('/foo bar.jpg')->setParams(['w' => 100])->getUrl();

        $this->assertEquals('/img/the-url-for-path', $url);
    }

    /** @test */
    public function it_gets_url_for_absolute_url()
    {
        $this->generator->shouldReceive('generateByUrl')
            ->with('http://example.com/external.jpg', ['w' => 100])
            ->andReturn('the-url-for-external');

        $url = $this->manipulator->setSource('http://example.com/external.jpg')->setParams(['w' => 100])->getUrl();

        $this->assertEquals('/img/the-url-for-external', $url);
    }

    /** @test */
    public function it_gets_url_for_asset()
    {
        $asset = new Asset;
        $asset->container((new AssetContainer)->handle('main'));
        $asset->path('img/foo.jpg');

        $this->generator->shouldReceive('generateByAsset')
            ->with($asset, ['w' => 100])
            ->andReturn('the-url-for-asset');

        $url = $this->manipulator->setSource($asset)->setParams(['w' => 100])->getUrl();

        $this->assertEquals('/img/the-url-for-asset', $url);
    }

    /** @test */
    public function it_gets_url_for_asset_id()
    {
        $asset = new Asset;
        $asset->container((new AssetContainer)->handle('main'));
        $asset->path('img/foo.jpg');

        Assets::shouldReceive('findOrFail')->with('main::img/foo.jpg')->andReturn($asset);

        $this->generator->shouldReceive('generateByAsset')
            ->with($asset, ['w' => 100])
            ->andReturn('the-url-for-id');

        $url = $this->manipulator->setSource('main::img/foo.jpg')->setParams(['w' => 100])->getUrl();

        $this->assertEquals('/img/the-url-for-id', $url);
    }

    /** @test */
    public function it_throws_exception_when_getting_url_with_invalid_asset_id()
    {
        $this->expectException(AssetNotFoundException::class);

        $asset = new Asset;
        $asset->container((new AssetContainer)->handle('main'));
        $asset->path('img/foo.jpg');

        $url = $this->manipulator->setSource('main::unknown.jpg')->setParams(['w' => 100])->getUrl();

        $this->assertEquals('/img/the-url-for-id', $url);
    }

    /**
     * @test
     *
     * @dataProvider prefixProvider
     */
    public function the_url_is_prefixed_based_on_the_config($prefix, $expected)
    {
        $generator = $this->generator
            ->shouldReceive('generateByPath')
            ->with('foo.jpg', [])
            ->andReturn('foo.jpg')
            ->getMock();

        $manipulator = $this->manipulator(['url' => $prefix], $generator)
            ->setSource('foo.jpg')
            ->setParams([]);

        $this->assertEquals($expected, $manipulator->getUrl());
    }

    public static function prefixProvider()
    {
        return [
            ['img', '/img/foo.jpg'],
            ['/img', '/img/foo.jpg'],
            ['img/', '/img/foo.jpg'],
        ];
    }

    /** @test */
    public function it_gets_attributes_for_path()
    {
        $this->generator->shouldReceive('generateByPath')
            ->with('/foo bar.jpg', ['w' => 100])
            ->andReturn('the-path-for-path');

        $manipulator = $this->manipulator->setSource('/foo bar.jpg')->setParams(['w' => 100]);

        Attributes::shouldReceive('from')
            ->with($manipulator->getCacheDisk(), 'the-path-for-path')
            ->once()
            ->andReturn(['test' => 'a']);

        $this->assertEquals(['test' => 'a'], $manipulator->getAttributes());
    }

    /** @test */
    public function it_gets_attributes_for_absolute_url()
    {
        $this->generator->shouldReceive('generateByUrl')
            ->with('http://example.com/external.jpg', ['w' => 100])
            ->andReturn('the-path-for-external');

        $manipulator = $this->manipulator->setSource('http://example.com/external.jpg')->setParams(['w' => 100]);

        Attributes::shouldReceive('from')
            ->with($manipulator->getCacheDisk(), 'the-path-for-external')
            ->once()
            ->andReturn(['test' => 'b']);

        $this->assertEquals(['test' => 'b'], $manipulator->getAttributes());
    }

    /** @test */
    public function it_gets_attributes_for_asset()
    {
        $asset = new Asset;
        $asset->container((new AssetContainer)->handle('main'));
        $asset->path('img/foo.jpg');

        $this->generator->shouldReceive('generateByAsset')
            ->with($asset, ['w' => 100])
            ->andReturn('the-path-for-asset');

        $manipulator = $this->manipulator->setSource($asset)->setParams(['w' => 100]);

        Attributes::shouldReceive('from')
            ->with($manipulator->getCacheDisk(), 'the-path-for-asset')
            ->once()
            ->andReturn(['test' => 'c']);

        $this->assertEquals(['test' => 'c'], $manipulator->getAttributes());
    }

    /** @test */
    public function it_gets_attributes_for_asset_id()
    {
        $asset = new Asset;
        $asset->container((new AssetContainer)->handle('main'));
        $asset->path('img/foo.jpg');

        Assets::shouldReceive('findOrFail')->with('main::img/foo.jpg')->andReturn($asset);

        $this->generator->shouldReceive('generateByAsset')
            ->with($asset, ['w' => 100])
            ->andReturn('the-path-for-id');

        $manipulator = $this->manipulator->setSource('main::img/foo.jpg')->setParams(['w' => 100]);

        Attributes::shouldReceive('from')
            ->with($manipulator->getCacheDisk(), 'the-path-for-id')
            ->once()
            ->andReturn(['test' => 'd']);

        $this->assertEquals(['test' => 'd'], $manipulator->getAttributes());
    }

    /** @test */
    public function it_throws_exception_when_getting_attributes_with_invalid_asset_id()
    {
        $this->expectException(AssetNotFoundException::class);

        $asset = new Asset;
        $asset->container((new AssetContainer)->handle('main'));
        $asset->path('img/foo.jpg');

        $this->manipulator->setSource('main::unknown.jpg')->setParams(['w' => 100])->getAttributes();
    }

    public function it_can_mark_with_asset()
    {
        $this->markTestIncomplete();
    }
}

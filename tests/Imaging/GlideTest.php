<?php

namespace Tests\Imaging;

use Facades\Statamic\Imaging\GlideServer;
use InvalidArgumentException;
use League\Flysystem\Local\LocalFilesystemAdapter;
use Statamic\Contracts\Imaging\UrlBuilder;
use Statamic\Imaging\GlideUrlBuilder;
use Statamic\Imaging\StaticUrlBuilder;
use Tests\TestCase;

class GlideTest extends TestCase
{
    /** @test */
    public function cache_false_will_make_a_filesystem_in_the_storage_directory()
    {
        config([
            'statamic.assets.image_manipulation.route' => 'imgs',
            'statamic.assets.image_manipulation.cache' => false,
            'statamic.assets.image_manipulation.cache_path' => public_path('img'), // irrelevant
        ]);

        $cache = GlideServer::create()->getCache();

        $this->assertLocalAdapter($adapter = $this->getAdapterFromFilesystem($cache));
        $this->assertEquals('public', $this->defaultFolderVisibility($cache));
        $this->assertEquals(storage_path('statamic/glide/'), $this->getRootFromLocalAdapter($adapter));
        $this->assertInstanceOf(GlideUrlBuilder::class, $this->app[UrlBuilder::class]);
        $this->assertEquals('/imgs', GlideServer::url());
    }

    /** @test */
    public function cache_true_will_make_a_filesystem_using_the_cache_path_location()
    {
        config([
            'statamic.assets.image_manipulation.route' => 'imgs',
            'statamic.assets.image_manipulation.cache' => true,
            'statamic.assets.image_manipulation.cache_path' => public_path('imgcache'),
        ]);

        $cache = GlideServer::create()->getCache();

        $this->assertLocalAdapter($adapter = $this->getAdapterFromFilesystem($cache));
        $this->assertEquals('public', $this->defaultFolderVisibility($cache));
        $this->assertEquals(public_path('imgcache/'), $this->getRootFromLocalAdapter($adapter));
        $this->assertInstanceOf(StaticUrlBuilder::class, $this->app[UrlBuilder::class]);
        $this->assertEquals('/imgs', GlideServer::url());
    }

    /** @test */
    public function cache_string_will_use_a_corresponding_filesystem()
    {
        config([
            'statamic.assets.image_manipulation.route' => 'imgs',
            'statamic.assets.image_manipulation.cache' => 'glidecache',
            'filesystems.disks.glidecache' => [
                'driver' => 'local',
                'root' => public_path('diskimgroot'),
                'url' => 'http://the-glide-url',
            ],
        ]);

        $cache = GlideServer::create()->getCache();

        $this->assertLocalAdapter($adapter = $this->getAdapterFromFilesystem($cache));
        $this->assertEquals(public_path('diskimgroot/'), $this->getRootFromLocalAdapter($adapter));
        $this->assertInstanceOf(StaticUrlBuilder::class, $this->app[UrlBuilder::class]);
        $this->assertEquals('http://the-glide-url', GlideServer::url());
    }

    /** @test */
    public function invalid_cache_string_will_throw_exception()
    {
        $this->expectExceptionMessage(InvalidArgumentException::class);
        $this->expectExceptionMessage('Disk [glidecache] does not have a configured driver.');

        config(['statamic.assets.image_manipulation.cache' => 'glidecache']);

        GlideServer::create()->getCache();
    }

    private function assertLocalAdapter($adapter)
    {
        $this->assertInstanceOf(LocalFilesystemAdapter::class, $adapter);

        // todo: flysystem v1
    }

    private function defaultFolderVisibility($filesystem)
    {
        $adapter = $this->getAdapterFromFilesystem($filesystem);

        $reflection = new \ReflectionClass($adapter);
        $visibilityConverter = $reflection->getProperty('visibility');
        $visibilityConverter->setAccessible(true);
        $visibilityConverter = $visibilityConverter->getValue($adapter);

        $reflection = new \ReflectionClass($visibilityConverter);
        $visibility = $reflection->getProperty('defaultForDirectories');
        $visibility->setAccessible(true);

        return $visibility->getValue($visibilityConverter);
    }

    private function getAdapterFromFilesystem($filesystem)
    {
        $reflection = new \ReflectionClass($filesystem);
        $property = $reflection->getProperty('adapter');
        $property->setAccessible(true);

        return $property->getValue($filesystem);
    }

    private function getRootFromLocalAdapter($adapter)
    {
        $reflection = new \ReflectionClass($adapter);
        $property = $reflection->getProperty('prefixer');
        $property->setAccessible(true);
        $prefixer = $property->getValue($adapter);

        return $prefixer->prefixPath('');
    }
}

<?php

namespace Tests\Imaging;

use InvalidArgumentException;
use League\Flysystem\Adapter\Local;
use League\Flysystem\Local\LocalFilesystemAdapter;
use Statamic\Contracts\Imaging\UrlBuilder;
use Statamic\Facades\Glide;
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

        $cache = Glide::server()->getCache();

        $this->assertLocalAdapter($adapter = $this->getAdapterFromFilesystem($cache));
        $this->assertEquals('public', $this->defaultFolderVisibility($cache));
        $this->assertEquals(storage_path('statamic/glide').DIRECTORY_SEPARATOR, $this->getRootFromLocalAdapter($adapter));
        $this->assertInstanceOf(GlideUrlBuilder::class, $this->app[UrlBuilder::class]);
        $this->assertEquals('/imgs', Glide::url());
    }

    /** @test */
    public function cache_true_will_make_a_filesystem_using_the_cache_path_location()
    {
        config([
            'statamic.assets.image_manipulation.route' => 'imgs',
            'statamic.assets.image_manipulation.cache' => true,
            'statamic.assets.image_manipulation.cache_path' => public_path('imgcache'),
        ]);

        $cache = Glide::server()->getCache();

        $this->assertLocalAdapter($adapter = $this->getAdapterFromFilesystem($cache));
        $this->assertEquals('public', $this->defaultFolderVisibility($cache));
        $this->assertEquals(public_path('imgcache').DIRECTORY_SEPARATOR, $this->getRootFromLocalAdapter($adapter));
        $this->assertInstanceOf(StaticUrlBuilder::class, $this->app[UrlBuilder::class]);
        $this->assertEquals('/imgs', Glide::url());
    }

    /** @test */
    public function cache_true_without_cache_path_will_throw_exception()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Image manipulation cache path is not defined.');

        config([
            'statamic.assets.image_manipulation.route' => 'imgs',
            'statamic.assets.image_manipulation.cache' => true,
            'statamic.assets.image_manipulation.cache_path' => null,
        ]);

        Glide::server()->getCache();
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

        $cache = Glide::server()->getCache();

        $this->assertLocalAdapter($adapter = $this->getAdapterFromFilesystem($cache));
        $this->assertEquals(public_path('diskimgroot').DIRECTORY_SEPARATOR, $this->getRootFromLocalAdapter($adapter));
        $this->assertInstanceOf(StaticUrlBuilder::class, $this->app[UrlBuilder::class]);
        $this->assertEquals('http://the-glide-url', Glide::url());
    }

    /** @test */
    public function invalid_cache_string_will_throw_exception()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Disk [glidecache] does not have a configured driver.');

        config(['statamic.assets.image_manipulation.cache' => 'glidecache']);

        Glide::server()->getCache();
    }

    private function assertLocalAdapter($adapter)
    {
        if ($this->isUsingFlysystemV1()) {
            return $this->assertInstanceOf(Local::class, $adapter);
        }

        $this->assertInstanceOf(LocalFilesystemAdapter::class, $adapter);
    }

    private function isUsingFlysystemV1()
    {
        return class_exists('\League\Flysystem\Util');
    }

    private function defaultFolderVisibility($filesystem)
    {
        if ($this->isUsingFlysystemV1()) {
            return 'public'; // irrelevant in v1
        }

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
        if ($this->isUsingFlysystemV1()) {
            return $adapter->getPathPrefix();
        }

        $reflection = new \ReflectionClass($adapter);
        $property = $reflection->getProperty('prefixer');
        $property->setAccessible(true);
        $prefixer = $property->getValue($adapter);

        return $prefixer->prefixPath('');
    }
}

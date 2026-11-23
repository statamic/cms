<?php

namespace Tests\Assets;

use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use League\Flysystem\DecoratedAdapter;
use League\Flysystem\Filesystem;
use League\Flysystem\Local\LocalFilesystemAdapter;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Facades\AssetContainer;
use Tests\PreventSavingStacheItemsToDisk;
use Tests\TestCase;

class AssetContainerContentsTest extends TestCase
{
    use PreventSavingStacheItemsToDisk;

    #[Test]
    #[DataProvider('listingCaching')]
    public function it_caches_the_listing_unless_the_watcher_is_enabled_on_a_local_disk(bool $watcher, bool $remote, bool $cached)
    {
        config(['statamic.stache.watcher' => $watcher]);

        Storage::fake('test');
        Storage::disk('test')->put('one.txt', '');

        if ($remote) {
            $adapter = new class(new LocalFilesystemAdapter(Storage::disk('test')->path(''))) extends DecoratedAdapter
            {
            };

            Storage::set('test', new FilesystemAdapter(new Filesystem($adapter), $adapter, []));
        }

        $container = tap(AssetContainer::make('test')->disk('test'))->save();

        $this->assertEquals(['one.txt'], $container->contents()->all()->keys()->all());
        $this->assertSame($cached, Cache::has('asset-list-contents-test'));
    }

    public static function listingCaching(): array
    {
        return [
            'watcher disabled, local disk' => [false, false, true],
            'watcher disabled, remote disk' => [false, true, true],
            'watcher enabled, local disk' => [true, false, false],
            'watcher enabled, remote disk' => [true, true, true],
        ];
    }
}

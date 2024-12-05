<?php

namespace Tests\Assets;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Assets\AssetRepository;
use Statamic\Contracts\Assets\Asset as AssetContract;
use Statamic\Exceptions\AssetNotFoundException;
use Statamic\Facades\Asset;
use Statamic\Facades\AssetContainer;
use Tests\PreventSavingStacheItemsToDisk;
use Tests\TestCase;

class AssetRepositoryTest extends TestCase
{
    use PreventSavingStacheItemsToDisk;

    #[Test]
    public function it_saves_the_meta_file_to_disk()
    {
        $disk = Storage::fake('test');

        $file = UploadedFile::fake()->image('image.jpg', 30, 60); // creates a 723 byte image
        Storage::disk('test')->putFileAs('foo', $file, 'image.jpg');
        $realFilePath = Storage::disk('test')->path('foo/image.jpg');
        touch($realFilePath, $timestamp = Carbon::now()->subMinutes(3)->timestamp);

        $container = tap(AssetContainer::make('test')->disk('test'))->save();
        $asset = $container->makeAsset('foo/image.jpg');
        $disk->assertMissing('foo/.meta/image.jpg.yaml');

        (new AssetRepository)->save($asset);

        $disk->assertExists($path = 'foo/.meta/image.jpg.yaml');
        $contents = <<<EOT
data: {  }
size: 723
last_modified: $timestamp
width: 30
height: 60
mime_type: image/jpeg
duration: null

EOT;
        $this->assertEquals($contents, $disk->get($path));
    }

    #[Test]
    public function it_resolves_the_correct_disk_from_similar_names()
    {
        Storage::fake('disk_long', ['url' => 'test_long_url_same_beginning']);
        Storage::fake('disk_short', ['url' => 'test']);

        $assetRepository = new AssetRepository;

        $file = UploadedFile::fake()->image('image.jpg', 30, 60); // creates a 723 byte image

        Storage::disk('disk_short')->putFileAs('foo', $file, 'image_in_short.jpg');
        $realFilePath = Storage::disk('disk_short')->path('foo/image_in_short.jpg');
        touch($realFilePath, $timestamp = Carbon::now()->subMinutes(3)->timestamp);

        $containerShortUrl = tap(AssetContainer::make('container_short')->disk('disk_short'))->save();
        $assetShortUrl = $containerShortUrl->makeAsset('foo/image_in_short.jpg');
        $assetRepository->save($assetShortUrl);

        Storage::disk('disk_long')->putFileAs('foo', $file, 'image_in_long.jpg');
        $realFilePath = Storage::disk('disk_long')->path('foo/image_in_long.jpg');
        touch($realFilePath, $timestamp = Carbon::now()->subMinutes(3)->timestamp);

        $containerLongUrl = tap(AssetContainer::make('container_long')->disk('disk_long'))->save();
        $assetLongUrl = $containerLongUrl->makeAsset('foo/image_in_long.jpg');
        $assetRepository->save($assetLongUrl);

        $foundAssetShortUrl = Asset::findByUrl($assetShortUrl->url());
        $this->assertInstanceOf(\Statamic\Contracts\Assets\Asset::class, $foundAssetShortUrl);
        $this->assertEquals('test/foo/image_in_short.jpg', $foundAssetShortUrl->url());

        $foundAssetLongUrl = Asset::findByUrl($assetLongUrl->url());
        $this->assertInstanceOf(\Statamic\Contracts\Assets\Asset::class, $foundAssetLongUrl);
        $this->assertEquals('test_long_url_same_beginning/foo/image_in_long.jpg', $foundAssetLongUrl->url());
    }

    #[Test]
    public function it_finds_assets_using_find_or_fail()
    {
        Storage::fake('disk_short', ['url' => 'test']);

        $assetRepository = new AssetRepository;

        $file = UploadedFile::fake()->image('image.jpg', 30, 60); // creates a 723 byte image

        Storage::disk('disk_short')->putFileAs('foo', $file, 'image_in_short.jpg');
        $realFilePath = Storage::disk('disk_short')->path('foo/image_in_short.jpg');
        touch($realFilePath, $timestamp = Carbon::now()->subMinutes(3)->timestamp);

        $containerShortUrl = tap(AssetContainer::make('container_short')->disk('disk_short'))->save();
        $assetShortUrl = $containerShortUrl->makeAsset('foo/image_in_short.jpg');
        $assetRepository->save($assetShortUrl);

        $asset = $assetRepository->findOrFail($assetShortUrl->id());

        $this->assertInstanceOf(AssetContract::class, $asset);
        $this->assertEquals($assetShortUrl->id(), $asset->id());
    }

    #[Test]
    public function test_find_or_fail_throws_exception_when_asset_does_not_exist()
    {
        $assetRepository = new AssetRepository;

        $this->expectException(AssetNotFoundException::class);
        $this->expectExceptionMessage('Asset [does-not-exist] not found');

        $assetRepository->findOrFail('does-not-exist');
    }
}

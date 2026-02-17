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
use Statamic\Facades\YAML;
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
        $this->assertEquals('/test/foo/image_in_short.jpg', $foundAssetShortUrl->url());

        $foundAssetLongUrl = Asset::findByUrl($assetLongUrl->url());
        $this->assertInstanceOf(\Statamic\Contracts\Assets\Asset::class, $foundAssetLongUrl);
        $this->assertEquals('/test_long_url_same_beginning/foo/image_in_long.jpg', $foundAssetLongUrl->url());
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
    public function it_finds_assets_by_id_when_the_path_contains_windows_separators()
    {
        Storage::fake('test');
        Storage::disk('test')->put('foo/bar.jpg', UploadedFile::fake()->image('bar.jpg')->getContent());

        $container = tap(AssetContainer::make('test_container')->disk('test'))->save();
        $asset = tap($container->makeAsset('foo/bar.jpg'))->save();

        $found = (new AssetRepository)->find('test_container::foo\\bar.jpg');

        $this->assertInstanceOf(AssetContract::class, $found);
        $this->assertEquals($asset->id(), $found->id());
    }

    #[Test]
    public function test_find_or_fail_throws_exception_when_asset_does_not_exist()
    {
        $assetRepository = new AssetRepository;

        $this->expectException(AssetNotFoundException::class);
        $this->expectExceptionMessage('Asset [does-not-exist] not found');

        $assetRepository->findOrFail('does-not-exist');
    }

    #[Test]
    public function it_preserves_other_locales_when_saving_a_localized_asset()
    {
        $this->setSites([
            'en' => ['url' => '/', 'locale' => 'en'],
            'es' => ['url' => '/es/', 'locale' => 'es'],
        ]);

        $disk = Storage::fake('test');
        $disk->put('foo/test.txt', 'hello');
        $disk->put('foo/.meta/test.txt.yaml', YAML::dump([
            'data' => [
                'en' => ['alt' => 'Bob Ross'],
            ],
            'size' => 5,
            'last_modified' => 123,
            'width' => null,
            'height' => null,
            'mime_type' => 'text/plain',
            'duration' => null,
        ]));

        $container = tap(AssetContainer::make('test')->disk('test')->localizable(true))->save();
        $asset = $container->makeAsset('foo/test.txt')->in('es');
        $asset->data(['alt' => 'El Bob Rosso']);

        (new AssetRepository)->save($asset);

        $meta = YAML::parse($disk->get('foo/.meta/test.txt.yaml'));

        $this->assertSame('Bob Ross', $meta['data']['en']['alt']);
        $this->assertSame('El Bob Rosso', $meta['data']['es']['alt']);
    }

    #[Test]
    public function it_does_not_materialize_inherited_values_when_saving_without_localized_changes()
    {
        $this->setSites([
            'en' => ['url' => '/', 'locale' => 'en'],
            'es' => ['url' => '/es/', 'locale' => 'es'],
        ]);

        $disk = Storage::fake('test');
        $disk->put('foo/test.txt', 'hello');
        $disk->put('foo/.meta/test.txt.yaml', YAML::dump([
            'data' => [
                'en' => ['alt' => 'Bob Ross'],
            ],
            'size' => 5,
            'last_modified' => 123,
            'width' => null,
            'height' => null,
            'mime_type' => 'text/plain',
            'duration' => null,
        ]));

        $container = tap(AssetContainer::make('test')->disk('test')->localizable(true))->save();
        $asset = $container->makeAsset('foo/test.txt')->in('es');

        (new AssetRepository)->save($asset);

        $meta = YAML::parse($disk->get('foo/.meta/test.txt.yaml'));

        $this->assertArrayHasKey('en', $meta['data']);
        $this->assertArrayNotHasKey('es', $meta['data']);
    }

    #[Test]
    public function it_drops_empty_localized_buckets_and_omits_default_sites_map()
    {
        $this->setSites([
            'en' => ['url' => '/', 'locale' => 'en'],
            'es' => ['url' => '/es/', 'locale' => 'es'],
            'fr' => ['url' => '/fr/', 'locale' => 'fr'],
        ]);

        $disk = Storage::fake('test');
        $disk->put('foo/test.txt', 'hello');
        $disk->put('foo/.meta/test.txt.yaml', YAML::dump([
            'data' => [
                'en' => ['alt' => 'Bob Ross'],
                'es' => ['alt' => 'El Bob Rosso'],
            ],
            'sites' => [
                'en' => null,
                'es' => 'en',
                'fr' => 'en',
            ],
            'size' => 5,
            'last_modified' => 123,
            'width' => null,
            'height' => null,
            'mime_type' => 'text/plain',
            'duration' => null,
        ]));

        $container = tap(AssetContainer::make('test')->disk('test')->localizable(true))->save();
        $asset = $container->makeAsset('foo/test.txt')->in('es');
        $asset->data([]);

        (new AssetRepository)->save($asset);

        $meta = YAML::parse($disk->get('foo/.meta/test.txt.yaml'));

        $this->assertArrayHasKey('en', $meta['data']);
        $this->assertArrayNotHasKey('es', $meta['data']);
        $this->assertArrayNotHasKey('sites', $meta);
    }

    #[Test]
    public function it_persists_sites_map_when_it_differs_from_default_site_origins()
    {
        $this->setSites([
            'en' => ['url' => '/', 'locale' => 'en'],
            'es' => ['url' => '/es/', 'locale' => 'es'],
        ]);

        $disk = Storage::fake('test');
        $disk->put('foo/test.txt', 'hello');
        $disk->put('foo/.meta/test.txt.yaml', YAML::dump([
            'data' => [
                'es' => ['alt' => 'El Bob Rosso'],
            ],
            'sites' => [
                'en' => 'es',
                'es' => null,
            ],
            'size' => 5,
            'last_modified' => 123,
            'width' => null,
            'height' => null,
            'mime_type' => 'text/plain',
            'duration' => null,
        ]));

        $container = tap(AssetContainer::make('test')->disk('test')->localizable(true))->save();
        $asset = $container->makeAsset('foo/test.txt')->in('en');
        $asset->data(['more_text' => 'custom']);

        (new AssetRepository)->save($asset);

        $meta = YAML::parse($disk->get('foo/.meta/test.txt.yaml'));

        $this->assertArrayHasKey('sites', $meta);
        $this->assertSame('es', $meta['sites']['en']);
        $this->assertNull($meta['sites']['es']);
    }

    #[Test]
    public function it_does_not_infinite_loop_when_sites_metadata_has_cycles()
    {
        $this->setSites([
            'en' => ['url' => '/', 'locale' => 'en'],
            'fr' => ['url' => '/fr/', 'locale' => 'fr'],
        ]);

        $disk = Storage::fake('test');
        $disk->put('foo/test.txt', 'hello');
        $disk->put('foo/.meta/test.txt.yaml', YAML::dump([
            'data' => [
                'en' => ['alt' => 'English alt'],
                'fr' => ['alt' => 'French alt'],
            ],
            'sites' => [
                'en' => 'fr',
                'fr' => 'en',
            ],
            'size' => 5,
            'last_modified' => 123,
            'width' => null,
            'height' => null,
            'mime_type' => 'text/plain',
            'duration' => null,
        ]));

        $container = tap(AssetContainer::make('test')->disk('test')->localizable(true))->save();
        $asset = $container->makeAsset('foo/test.txt')->in('en');

        $this->assertSame('English alt', $asset->get('alt'));
    }

    #[Test]
    public function focal_points_are_not_localized()
    {
        $this->setSites([
            'en' => ['url' => '/', 'locale' => 'en'],
            'es' => ['url' => '/es/', 'locale' => 'es'],
        ]);

        $disk = Storage::fake('test');
        $disk->put('foo/test.txt', 'hello');
        $disk->put('foo/.meta/test.txt.yaml', YAML::dump([
            'data' => [
                'en' => [
                    'alt' => 'Bob Ross',
                    'focus' => '10-20',
                ],
                'es' => [
                    'alt' => 'El Bob Rosso',
                ],
            ],
            'size' => 5,
            'last_modified' => 123,
            'width' => null,
            'height' => null,
            'mime_type' => 'text/plain',
            'duration' => null,
        ]));

        $container = tap(AssetContainer::make('test')->disk('test')->localizable(true))->save();
        $asset = $container->makeAsset('foo/test.txt')->in('es');
        $asset->set('focus', '75-25');

        (new AssetRepository)->save($asset);

        $meta = YAML::parse($disk->get('foo/.meta/test.txt.yaml'));

        $this->assertSame('75-25', $meta['data']['en']['focus']);
        $this->assertArrayNotHasKey('focus', $meta['data']['es']);
    }
}

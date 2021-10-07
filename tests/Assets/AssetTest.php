<?php

namespace Tests\Assets;

use Carbon\Carbon;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Storage;
use Statamic\Assets\Asset;
use Statamic\Assets\AssetContainer;
use Statamic\Events\AssetSaved;
use Statamic\Events\AssetUploaded;
use Statamic\Facades;
use Statamic\Facades\File;
use Statamic\Facades\YAML;
use Statamic\Fields\Blueprint;
use Tests\PreventSavingStacheItemsToDisk;
use Tests\TestCase;

class AssetTest extends TestCase
{
    use PreventSavingStacheItemsToDisk;

    public function setUp(): void
    {
        parent::setUp();

        // use the file cache driver so we can test that the cached file listings
        // are coming from the cache and not just the in-memory collection
        config(['cache.default' => 'file']);
        Cache::clear();

        config(['filesystems.disks.test' => [
            'driver' => 'local',
            'root' => __DIR__.'/tmp',
        ]]);

        $this->container = (new AssetContainer)
            ->handle('test_container')
            ->disk('test');

        Storage::fake('test');
        Storage::fake('dimensions-cache');
    }

    /** @test */
    public function it_sets_and_gets_data_values()
    {
        $asset = (new Asset)->container($this->container);
        $this->assertNull($asset->get('foo'));

        $return = $asset->set('foo', 'bar');

        $this->assertEquals($asset, $return);
        $this->assertTrue($asset->has('foo'));
        $this->assertEquals('bar', $asset->get('foo'));
        $this->assertEquals('fallback', $asset->get('unknown', 'fallback'));
    }

    /** @test */
    public function it_gets_and_sets_data_values_using_magic_properties()
    {
        $asset = (new Asset)->container($this->container);
        $this->assertNull($asset->foo);

        $asset->foo = 'bar';

        $this->assertTrue($asset->has('foo'));
        $this->assertEquals('bar', $asset->foo);
    }

    /** @test */
    public function it_gets_and_sets_all_data()
    {
        $asset = (new Asset)->container($this->container);
        $this->assertEquals([], $asset->data()->all());

        $return = $asset->data(['foo' => 'bar']);

        $this->assertEquals($asset, $return);
        $this->assertEquals(['foo' => 'bar'], $asset->data()->all());
    }

    /** @test */
    public function it_sets_and_gets_the_container()
    {
        $asset = new Asset;
        $this->assertNull($asset->container());

        $return = $asset->container($container = new AssetContainer);

        $this->assertEquals($asset, $return);
        $this->assertEquals($container, $asset->container());
    }

    /** @test */
    public function it_gets_the_container_if_provided_with_a_string()
    {
        Facades\AssetContainer::shouldReceive('find')
            ->with('test')
            ->andReturn($container = new AssetContainer);

        $asset = (new Asset)->container('test');

        $this->assertEquals($container, $asset->container());
    }

    /** @test */
    public function it_gets_the_container_id()
    {
        $asset = (new Asset)->container(
            $container = (new AssetContainer)->handle('test')
        );

        $this->assertEquals('test', $asset->containerId());
    }

    /** @test */
    public function it_gets_and_sets_the_path()
    {
        $asset = new Asset;
        $this->assertNull($asset->path());

        $return = $asset->path('path/to/asset.jpg');

        $this->assertEquals($asset, $return);
        $this->assertEquals('path/to/asset.jpg', $asset->path());

        $this->assertEquals('asset.jpg', $asset->path('/asset.jpg')->path());
        $this->assertEquals('asset.jpg', $asset->path('//asset.jpg')->path());
        $this->assertEquals('asset.jpg', $asset->path('asset.jpg')->path());
    }

    /** @test */
    public function it_gets_the_id_from_the_container_and_path()
    {
        $asset = (new Asset)
            ->container((new AssetContainer)->handle('123'))
            ->path('path/to/asset.jpg');

        $this->assertEquals('123::path/to/asset.jpg', $asset->id());
        $this->assertEquals('asset::123::path/to/asset.jpg', $asset->reference());
    }

    /** @test */
    public function it_gets_the_disk_from_the_container()
    {
        $container = $this->mock(AssetContainer::class);
        $container->shouldReceive('disk')->andReturn('test');

        $asset = (new Asset)->container($container);

        $this->assertEquals('test', $asset->disk());
    }

    /** @test */
    public function it_checks_if_it_exists()
    {
        $disk = Storage::fake('test');
        $disk->put('yes.txt', '');
        $existing = (new Asset)->container($this->container)->path('yes.txt');
        $nonExistent = (new Asset)->container($this->container)->path('no.txt');
        $noPath = (new Asset)->container($this->container);

        $this->assertTrue($existing->exists());
        $this->assertFalse($nonExistent->exists());
        $this->assertFalse($noPath->exists());
    }

    /** @test */
    public function it_gets_the_filename()
    {
        $this->assertEquals('asset', (new Asset)->path('path/to/asset.jpg')->filename());
    }

    /** @test */
    public function it_gets_the_basename()
    {
        $this->assertEquals('asset.jpg', (new Asset)->path('path/to/asset.jpg')->basename());
    }

    /** @test */
    public function it_gets_the_folder_name()
    {
        $this->assertEquals('/', (new Asset)->path('asset.jpg')->folder());
        $this->assertEquals('path/to', (new Asset)->path('path/to/asset.jpg')->folder());
    }

    /** @test */
    public function it_gets_the_resolved_path()
    {
        $container = $this->mock(AssetContainer::class);
        $container->shouldReceive('diskPath')->andReturn('path/to/container');

        $asset = (new Asset)->container($container)->path('path/to/asset.jpg');

        $this->assertEquals('path/to/container/path/to/asset.jpg', $asset->resolvedPath());
    }

    /** @test */
    public function it_checks_if_an_extension_matches()
    {
        $asset = (new Asset)->path('asset.jpg');

        $this->assertTrue($asset->extensionIsOneof(['jpg']));
        $this->assertTrue($asset->extensionIsOneof(['jpg', 'txt']));
        $this->assertFalse($asset->extensionIsOneof(['txt', 'mp3']));
    }

    /** @test */
    public function it_gets_the_extension_guessed_extension_and_mime_type()
    {
        Storage::fake('test');
        Storage::disk('test')->put('.meta/foo.mp4a.yaml', YAML::dump(['mime_type' => 'audio/mp4']));

        $container = Facades\AssetContainer::make('test')->disk('test');

        $asset = (new Asset)->container($container)->path('foo.mp4a');

        $this->assertEquals('audio/mp4', $asset->mimeType());
        $this->assertEquals('m4a', $asset->guessedExtension());
        $this->assertEquals('mp4a', $asset->extension());
    }

    /** @test */
    public function it_checks_if_its_an_audio_file()
    {
        $extensions = ['aac', 'flac', 'm4a', 'mp3', 'ogg', 'wav'];

        foreach ($extensions as $ext) {
            $this->assertTrue((new Asset)->path("path/to/asset.$ext")->isAudio());
        }

        $this->assertFalse((new Asset)->path('path/to/asset.jpg')->isAudio());
    }

    /** @test */
    public function it_checks_if_its_a_video_file()
    {
        $extensions = ['h264', 'mp4', 'm4v', 'ogv', 'webm'];

        foreach ($extensions as $ext) {
            $this->assertTrue((new Asset)->path("path/to/asset.$ext")->isVideo());
        }

        $this->assertFalse((new Asset)->path('path/to/asset.jpg')->isVideo());
    }

    /** @test */
    public function it_checks_if_its_an_image_file()
    {
        $extensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];

        foreach ($extensions as $ext) {
            $this->assertTrue((new Asset)->path("path/to/asset.$ext")->isImage());
        }

        $this->assertFalse((new Asset)->path('path/to/asset.txt')->isImage());
    }

    /** @test */
    public function it_checks_if_it_can_be_previewed_in_google_docs_previewer()
    {
        $extensions = [
            'doc', 'docx', 'pages', 'txt', 'ai', 'psd', 'eps', 'ps', 'css', 'html', 'php', 'c', 'cpp', 'h', 'hpp', 'js',
            'ppt', 'pptx', 'flv', 'tiff', 'ttf', 'dxf', 'xps', 'zip', 'rar', 'xls', 'xlsx',
        ];

        foreach ($extensions as $ext) {
            $this->assertTrue((new Asset)->path("path/to/asset.$ext")->isPreviewable());
        }

        $this->assertFalse((new Asset)->path('path/to/asset.jpg')->isPreviewable());
    }

    /** @test */
    public function it_gets_last_modified_time()
    {
        Carbon::setTestNow('2017-01-02 14:35:00');
        Storage::disk('test')->put('test.txt', '');
        touch(
            Storage::disk('test')->getAdapter()->getPathPrefix().'test.txt',
            Carbon::now()->timestamp
        );

        $asset = (new Asset)->container($this->container)->path('test.txt');

        $lastModified = $asset->lastModified();
        $this->assertInstanceOf(Carbon::class, $lastModified);
        $this->assertEquals(Carbon::parse('2017-01-02 14:35:00')->timestamp, $lastModified->timestamp);
    }

    /** @test */
    public function it_generates_and_clears_meta_caches()
    {
        Storage::fake('test');
        Storage::disk('test')->put('foo/test.txt', '');
        Storage::disk('test')->put('foo/.meta/test.txt.yaml', YAML::dump($expected = [
            'data' => ['foo' => 'bar'],
            'size' => 123,
        ]));

        $container = tap(Facades\AssetContainer::make('test')->disk('test'))->save();
        $asset = (new Asset)->container($container)->path('foo/test.txt');

        // Caches shouldn't be generated until asked for...
        $this->assertFalse(Cache::has($asset->metaCacheKey()));

        // Asking for meta results in it being cached
        $asset->meta();
        $this->assertTrue(Cache::has($asset->metaCacheKey()));

        // Deleting asset should clear cache
        $asset->delete();
        $this->assertFalse(Cache::has($asset->metaCacheKey()));
    }

    /** @test */
    public function it_gets_existing_meta_data()
    {
        Storage::fake('test');
        Storage::disk('test')->put('foo/test.txt', '');
        Storage::disk('test')->put('foo/.meta/test.txt.yaml', YAML::dump($expected = [
            'data' => ['foo' => 'bar'],
            'size' => 123,
        ]));

        $container = Facades\AssetContainer::make('test')->disk('test');
        $asset = (new Asset)->container($container)->path('foo/test.txt');

        // If we haven't yet asked for meta, it should not exist in cache...
        $this->assertNull(Cache::get($asset->metaCacheKey()));

        // After we ask for meta, we should see it in cache as well...
        $this->assertEquals($expected, $asset->meta());
        $this->assertEquals($expected, Cache::get($asset->metaCacheKey()));
    }

    /** @test */
    public function it_generates_meta_on_demand_if_it_doesnt_exist()
    {
        Storage::fake('test');
        Carbon::setTestNow(Carbon::parse('2012-01-02 5:00pm'));

        $file = UploadedFile::fake()->image('image.jpg', 30, 60); // creates a 723 byte image
        Storage::disk('test')->putFileAs('foo', $file, 'image.jpg');
        $realFilePath = Storage::disk('test')->getAdapter()->getPathPrefix().'foo/image.jpg';
        touch($realFilePath, Carbon::now()->subMinutes(3)->timestamp);

        $container = Facades\AssetContainer::make('test')->disk('test');
        Facades\AssetContainer::shouldReceive('findByHandle')->with('test')->andReturn($container);
        $asset = $container->makeAsset('foo/image.jpg')->set('foo', 'bar');

        $metaWithoutData = [
            'data' => [],
            'size' => 723,
            'last_modified' => Carbon::parse('2012-01-02 4:57pm')->timestamp,
            'width' => 30,
            'height' => 60,
            'mime_type' => 'image/jpeg',
        ];

        $metaWithData = [
            'data' => ['foo' => 'bar'],
            'size' => 723,
            'last_modified' => Carbon::parse('2012-01-02 4:57pm')->timestamp,
            'width' => 30,
            'height' => 60,
            'mime_type' => 'image/jpeg',
        ];

        // The meta that's saved to file will also be cached, but will not include in-memory data...
        $this->assertEquals($metaWithoutData, YAML::parse(Storage::disk('test')->get('foo/.meta/image.jpg.yaml')));
        $this->assertEquals($metaWithoutData, Cache::get($asset->metaCacheKey()));

        // Even though the meta data is not cached, we're still able able to get it off the intance...
        $this->assertEquals($metaWithData, $asset->meta());
        $this->assertEquals($metaWithoutData, Cache::get($asset->metaCacheKey()));

        // Saving should clear the cache and persist the new meta data to the filesystem...
        $asset->save();
        $this->assertNull(Cache::get($asset->metaCacheKey()));
        $this->assertEquals($metaWithData, YAML::parse(Storage::disk('test')->get('foo/.meta/image.jpg.yaml')));

        // Then if we ask for new meta, it should cache with the newly saved data...
        $this->assertEquals($metaWithData, $asset->meta());
        $this->assertEquals($metaWithData, Cache::get($asset->metaCacheKey()));
    }

    /** @test */
    public function it_generates_meta_on_demand_if_a_required_value_is_missing()
    {
        Storage::fake('test');

        $file = UploadedFile::fake()->image('image.jpg', 30, 60); // creates a 723 byte image
        Storage::disk('test')->putFileAs('foo', $file, 'image.jpg');
        $realFilePath = Storage::disk('test')->getAdapter()->getPathPrefix().'foo/image.jpg';
        touch($realFilePath, $timestamp = Carbon::parse('2021-02-22 09:41:42')->timestamp);

        $container = Facades\AssetContainer::make('test')->disk('test');
        $asset = (new Asset)->container($container)->path('foo/image.jpg');

        $incompleteMeta = [
            'data' => [],
        ];

        $completeMeta = [
            'data' => [],
            'size' => 723,
            'last_modified' => $timestamp,
            'width' => 30,
            'height' => 60,
            'mime_type' => 'image/jpeg',
        ];

        Storage::disk('test')->put('foo/.meta/image.jpg.yaml', YAML::dump($incompleteMeta));

        $asset->size();

        $this->assertEquals($completeMeta, YAML::parse(Storage::disk('test')->get('foo/.meta/image.jpg.yaml')));
    }

    /** @test */
    public function it_hydrates_data_from_meta_file()
    {
        $disk = Storage::fake('test');
        $disk->put('foo/test.txt', '');
        $disk->put('foo/.meta/test.txt.yaml', YAML::dump(['data' => ['hello' => 'world']]));

        $container = Facades\AssetContainer::make('test')->disk('test');
        $asset = (new Asset)->container($container)->path('foo/test.txt');

        $this->assertEquals(['hello' => 'world'], $asset->data()->all());
    }

    /** @test */
    public function it_saves()
    {
        Event::fake();
        Storage::fake('test');
        $container = Facades\AssetContainer::make('test')->disk('test');
        $asset = (new Asset)->container($container)->path('foo.jpg');
        Facades\Asset::shouldReceive('save')->with($asset);

        $return = $asset->save();

        $this->assertTrue($return);

        Event::assertDispatched(AssetSaved::class, function ($event) use ($asset) {
            return $event->asset = $asset;
        });

        // Assertion about the meta file is in the AssetRepository test
    }

    /** @test */
    public function it_doesnt_add_path_to_container_listing_if_it_doesnt_exist()
    {
        Facades\AssetContainer::shouldReceive('findByHandle')->with('test_container')->andReturn($this->container);

        $this->container->makeAsset('one/two/foo.jpg')->save();

        $this->assertEquals([], $this->container->contents()->cached()->keys()->all());
    }

    /** @test */
    public function it_doesnt_add_path_to_container_listing_if_it_doesnt_exist_with_asserts_disabled_and_using_local()
    {
        // the local adapter doesn't really matter. its just checking that if getMetadata
        // with asserts disabled returns throws an exception (which local does, but not s3).

        $this->container->disk()->filesystem()->getConfig()->set('disable_asserts', true);

        Facades\AssetContainer::shouldReceive('findByHandle')->with('test_container')->andReturn($this->container);

        $this->container->makeAsset('one/two/foo.jpg')->save();

        $this->assertEquals([], $this->container->contents()->cached()->keys()->all());
    }

    /** @test */
    public function it_doesnt_add_path_to_container_listing_if_it_doesnt_exist_with_asserts_disabled_and_using_s3()
    {
        // the s3 adapter doesn't really matter. its just checking that if getMetadata
        // with asserts disabled returns false (which s3 does, but local doesn't).

        // these mocks are ugly but it was simpler than setting up an s3 driver.
        // we just want to make sure getMetadata returns false.
        $driver = $this->mock(\League\Flysystem\Filesystem::class);
        $driver->shouldReceive('listContents')->andReturn([]);
        $driver->shouldReceive('getMetadata')->andReturnFalse(); // this is the meaningful line
        $filesystem = $this->mock(\Illuminate\Filesystem\FilesystemAdapter::class);
        $filesystem->shouldReceive('getDriver')->andReturn($driver);
        $disk = $this->mock(\Statamic\Filesystem\Filesystem::class);
        $disk->shouldReceive('filesystem')->andReturn($filesystem);
        $disk->shouldReceive('put');

        File::shouldReceive('disk')->with('test')->andReturn($disk);

        Facades\AssetContainer::shouldReceive('findByHandle')->with('test_container')->andReturn($this->container);

        $this->container->makeAsset('one/two/foo.jpg')->save();

        $this->assertEquals([], $this->container->contents()->cached()->keys()->all());
    }

    /** @test */
    public function it_deletes()
    {
        Storage::fake('local');
        $disk = Storage::disk('local');
        $disk->put('path/to/asset.txt', '');
        $disk->put('path/to/another-asset.txt', '');
        $container = Facades\AssetContainer::make('test')->disk('local');
        Facades\AssetContainer::shouldReceive('save')->with($container);
        Facades\AssetContainer::shouldReceive('findByHandle')->with('test')->andReturn($container);
        $asset = (new Asset)->container($container)->path('path/to/asset.txt');
        $disk->assertExists('path/to/asset.txt');
        $this->assertEquals([
            'path/to/another-asset.txt',
            'path/to/asset.txt',
        ], $container->files()->all());
        $this->assertEquals([
            'path/to/asset.txt' => [],
            'path/to/another-asset.txt' => [],
        ], $container->assets('/', true)->keyBy->path()->map(function ($item) {
            return $item->data()->all();
        })->all());

        $return = $asset->delete();

        $this->assertEquals($asset, $return);
        $disk->assertMissing('path/to/asset.txt');
        $this->assertEquals([
            'path/to/another-asset.txt',
        ], $container->files()->all());
        $this->assertEquals([
            'path/to/another-asset.txt' => [],
        ], $container->assets('/', true)->keyBy->path()->map(function ($item) {
            return $item->data()->all();
        })->all());

        $this->assertEquals([
            'path',
            'path/to',
            'path/to/another-asset.txt',
        ], $container->contents()->cached()->keys()->all());

        // TODO: Assert about event, or convert to a callback
    }

    /** @test */
    public function it_can_be_moved_to_another_folder()
    {
        Event::fake();
        Storage::fake('local');
        $disk = Storage::disk('local');
        $disk->put('old/asset.txt', 'The asset contents');
        $container = Facades\AssetContainer::make('test')->disk('local');
        Facades\AssetContainer::shouldReceive('save')->with($container);
        Facades\AssetContainer::shouldReceive('findByHandle')->with('test')->andReturn($container);
        $asset = $container->makeAsset('old/asset.txt')->data(['foo' => 'bar']);
        $asset->save();
        $oldMeta = $disk->get('old/.meta/asset.txt.yaml');
        $disk->assertExists('old/asset.txt');
        $disk->assertExists('old/.meta/asset.txt.yaml');
        $this->assertEquals([
            'old/asset.txt',
        ], $container->files()->all());
        $this->assertEquals([
            'old/asset.txt' => ['foo' => 'bar'],
        ], $container->assets('/', true)->keyBy->path()->map(function ($item) {
            return $item->data()->all();
        })->all());

        $return = $asset->move('new');

        $this->assertEquals($asset, $return);
        $disk->assertMissing('old/asset.txt');
        $disk->assertMissing('old/.meta/asset.txt.yaml');
        $disk->assertExists('new/asset.txt');
        $disk->assertExists('new/.meta/asset.txt.yaml');
        $this->assertEquals($oldMeta, $disk->get('new/.meta/asset.txt.yaml'));
        $this->assertEquals([
            'new/asset.txt',
        ], $container->files()->all());
        $this->assertEquals([
            'new/asset.txt' => ['foo' => 'bar'],
        ], $container->assets('/', true)->keyBy->path()->map(function ($item) {
            return $item->data()->all();
        })->all());
        $this->assertEquals([
            'old', // the empty directory doesnt actually get deleted
            'new',
            'new/asset.txt',
        ], $container->contents()->cached()->keys()->all());
        Event::assertDispatched(AssetSaved::class);
    }

    /** @test */
    public function it_can_be_moved_to_another_folder_with_a_new_filename()
    {
        Storage::fake('local');
        $disk = Storage::disk('local');
        $disk->put('old/asset.txt', 'The asset contents');
        $container = Facades\AssetContainer::make('test')->disk('local');
        Facades\AssetContainer::shouldReceive('save')->with($container);
        Facades\AssetContainer::shouldReceive('findByHandle')->with('test')->andReturn($container);
        $asset = $container->makeAsset('old/asset.txt')->data(['foo' => 'bar']);
        $asset->save();
        $oldMeta = $disk->get('old/.meta/asset.txt.yaml');
        $disk->assertExists('old/asset.txt');
        $disk->assertExists('old/.meta/asset.txt.yaml');
        $this->assertEquals([
            'old/asset.txt' => ['foo' => 'bar'],
        ], $container->assets('/', true)->keyBy->path()->map(function ($item) {
            return $item->data()->all();
        })->all());

        $return = $asset->move('new', 'newfilename');

        $this->assertEquals($asset, $return);
        $disk->assertMissing('old/asset.txt');
        $disk->assertMissing('old/.meta/asset.txt.yaml');
        $disk->assertExists('new/newfilename.txt');
        $disk->assertExists('new/.meta/newfilename.txt.yaml');
        $this->assertEquals($oldMeta, $disk->get('new/.meta/newfilename.txt.yaml'));
        $this->assertEquals([
            'new/newfilename.txt' => ['foo' => 'bar'],
        ], $container->assets('/', true)->keyBy->path()->map(function ($item) {
            return $item->data()->all();
        })->all());
        $this->assertEquals([
            'old', // the empty directory doesnt actually get deleted
            'new',
            'new/newfilename.txt',
        ], $container->contents()->cached()->keys()->all());
    }

    /** @test */
    public function it_renames()
    {
        Event::fake();
        $disk = Storage::fake('local');
        $disk->put('old/asset.txt', 'The asset contents');
        $container = Facades\AssetContainer::make('test')->disk('local');
        Facades\AssetContainer::shouldReceive('save')->with($container);
        Facades\AssetContainer::shouldReceive('findByHandle')->with('test')->andReturn($container);
        $asset = $container->makeAsset('old/asset.txt')->data(['foo' => 'bar']);
        $asset->save();
        $oldMeta = $disk->get('old/.meta/asset.txt.yaml');
        $disk->assertExists('old/asset.txt');
        $disk->assertExists('old/.meta/asset.txt.yaml');
        $this->assertEquals([
            'old/asset.txt',
        ], $container->files()->all());
        $this->assertEquals([
            'old/asset.txt' => ['foo' => 'bar'],
        ], $container->assets('/', true)->keyBy->path()->map(function ($item) {
            return $item->data()->all();
        })->all());

        $return = $asset->rename('newfilename');

        $this->assertEquals($asset, $return);
        $disk->assertMissing('old/asset.txt');
        $disk->assertMissing('old/.meta/asset.txt.yaml');
        $disk->assertExists('old/newfilename.txt');
        $disk->assertExists('old/.meta/newfilename.txt.yaml');
        $this->assertEquals($oldMeta, $disk->get('old/.meta/newfilename.txt.yaml'));
        $this->assertEquals([
            'old/newfilename.txt',
        ], $container->files()->all());
        $this->assertEquals([
            'old/newfilename.txt' => ['foo' => 'bar'],
        ], $container->assets('/', true)->keyBy->path()->map(function ($item) {
            return $item->data()->all();
        })->all());
        $this->assertEquals([
            'old',
            'old/newfilename.txt',
        ], $container->contents()->cached()->keys()->all());
        Event::assertDispatched(AssetSaved::class);
    }

    /** @test */
    public function it_gets_dimensions()
    {
        $file = UploadedFile::fake()->image('image.jpg', 30, 60);
        Storage::fake('test')->putFileAs('foo', $file, 'image.jpg');
        $asset = (new Asset)->path('foo/image.jpg')->container($this->container);

        $this->assertEquals([30, 60], $asset->dimensions());
        $this->assertEquals(30, $asset->width());
        $this->assertEquals(60, $asset->height());
        $this->assertEquals(0.5, $asset->ratio());
    }

    /** @test */
    public function it_gets_dimensions_for_svgs()
    {
        Storage::fake('test')->put('foo/image.svg', '<svg width="30" height="60"></svg>');
        $asset = (new Asset)->path('foo/image.svg')->container($this->container);

        $this->assertEquals([30, 60], $asset->dimensions());
        $this->assertEquals(30, $asset->width());
        $this->assertEquals(60, $asset->height());
        $this->assertEquals(0.5, $asset->ratio());
    }

    /** @test */
    public function it_gets_no_dimensions_for_non_images()
    {
        $file = UploadedFile::fake()->create('file.txt');
        Storage::fake('test')->putFileAs('foo', $file, 'file.txt');
        $asset = (new Asset)->path('foo/file.txt')->container($this->container);

        $this->assertEquals([null, null], $asset->dimensions());
        $this->assertEquals(null, $asset->width());
        $this->assertEquals(null, $asset->height());
        $this->assertEquals(null, $asset->ratio());
    }

    /** @test */
    public function it_doesnt_regenerate_the_meta_file_when_getting_non_image_dimensions()
    {
        $asset = $this->partialMock(Asset::class);

        $asset->shouldReceive('meta')->times(0);

        $this->assertEquals([null, null], $asset->dimensions());
    }

    /** @test */
    public function it_gets_file_size_in_bytes()
    {
        $container = $this->container;
        $size = filesize($fixture = __DIR__.'/__fixtures__/container/a.txt');
        copy($fixture, Storage::disk('test')->getAdapter()->getPathPrefix().'test.txt');

        $asset = (new Asset)
            ->container($this->container)
            ->path('test.txt');

        $this->assertEquals($size, $asset->size());
    }

    /** @test */
    public function it_compiles_augmented_array_data()
    {
        Facades\Blueprint::shouldReceive('find')
            ->with('assets/test_container')
            ->andReturn($blueprint = (new Blueprint)->setHandle('test_container')->setNamespace('assets'));

        $asset = (new Asset)
            ->container($this->container)
            ->set('title', 'test')
            ->setSupplement('foo', 'bar')
            ->path('path/to/asset.jpg');

        $array = $asset->toAugmentedArray();

        $this->assertArraySubset([
            'id' => 'test_container::path/to/asset.jpg',
            'title' => 'asset.jpg',
            'path' => 'path/to/asset.jpg',
            'filename' => 'asset',
            'basename' => 'asset.jpg',
            'extension' => 'jpg',
            'is_asset' => true,
            'folder' => 'path/to',
            'container' => $this->container,
            'blueprint' => $blueprint,
            'foo' => 'bar',
        ], $array);

        $keys = ['is_audio', 'is_previewable', 'is_image', 'is_video', 'edit_url', 'url'];
        foreach ($keys as $key) {
            $this->assertArrayHasKey($key, $array);
        }

        foreach ($this->toArrayKeysWhenFileExists() as $key) {
            $this->assertArrayNotHasKey($key, $array);
        }
    }

    /** @test */
    public function data_keys_get_added_to_array()
    {
        Facades\Blueprint::shouldReceive('find')
            ->with('assets/test_container')
            ->andReturn($blueprint = (new Blueprint)->setHandle('test_container')->setNamespace('assets'));

        $array = (new Asset)
            ->container($this->container)
            ->set('title', 'test')
            ->path('path/to/asset.jpg')
            ->set('foo', 'bar')
            ->set('bar', 'baz')
            ->toAugmentedArray();

        $this->assertEquals('bar', $array['foo']);
        $this->assertEquals('baz', $array['bar']);
    }

    /** @test */
    public function extra_keys_get_added_to_array_when_file_exists()
    {
        Facades\Blueprint::shouldReceive('find')
            ->with('assets/test_container')
            ->andReturn($blueprint = (new Blueprint)->setHandle('test_container')->setNamespace('assets'));

        $container = $this->container;
        Storage::disk('test')->put('test.txt', '');

        $asset = (new Asset)->container($container)->path('test.txt');

        $array = $asset->toAugmentedArray();
        foreach ($this->toArrayKeysWhenFileExists() as $key) {
            $this->assertArrayHasKey($key, $array);
        }
    }

    /** @test */
    public function can_use_set_focus_in_augmented_focus_css_value()
    {
        Facades\Blueprint::shouldReceive('find')
            ->with('assets/test_container')
            ->andReturn($blueprint = (new Blueprint)->setHandle('test_container')->setNamespace('assets'));

        $asset = (new Asset)
            ->container($this->container)
            ->path('path/to/asset.jpg')
            ->set('focus', '75-25');

        $this->assertSame($asset->augmentedValue('focus_css'), '75% 25%');
    }

    /** @test */
    public function can_fallback_to_default_augmented_focus_css_value_if_focus_not_set()
    {
        Facades\Blueprint::shouldReceive('find')
            ->with('assets/test_container')
            ->andReturn($blueprint = (new Blueprint)->setHandle('test_container')->setNamespace('assets'));

        $asset = (new Asset)
            ->container($this->container)
            ->path('path/to/asset.jpg');

        $this->assertSame($asset->augmentedValue('focus_css'), '50% 50%');
    }

    /** @test */
    public function it_can_upload_a_file_without_an_existing_cache()
    {
        $this->uploadFileTest();
    }

    /** @test */
    public function it_can_upload_a_file_with_an_existing_cache()
    {
        Cache::put('asset-list-contents-test_container', collect());
        $this->uploadFileTest();
    }

    private function uploadFileTest()
    {
        Event::fake();
        $asset = (new Asset)->container($this->container)->path('path/to/asset.jpg')->syncOriginal();

        Facades\AssetContainer::shouldReceive('findByHandle')->with('test_container')->andReturn($this->container);
        Storage::disk('test')->assertMissing('path/to/asset.jpg');

        $return = $asset->upload(UploadedFile::fake()->image('asset.jpg', 13, 15));

        $this->assertEquals($asset, $return);
        Storage::disk('test')->assertExists('path/to/asset.jpg');
        $this->assertEquals('path/to/asset.jpg', $asset->path());

        $meta = $asset->meta();
        $this->assertEquals(13, $meta['width']);
        $this->assertEquals(15, $meta['height']);
        $this->assertEquals('image/jpeg', $meta['mime_type']);
        $this->assertArrayHasKey('size', $meta);
        $this->assertArrayHasKey('last_modified', $meta);
        $this->assertEquals([
            'path/to/asset.jpg',
        ], $this->container->files()->all());
        $this->assertEquals([
            'path/to/asset.jpg' => [],
        ], $this->container->assets('/', true)->keyBy->path()->map(function ($item) {
            return $item->data()->all();
        })->all());
        $this->assertEquals([
            'path',
            'path/to',
            'path/to/asset.jpg',
        ], Cache::get('asset-list-contents-test_container')->keys()->all());
        Event::assertDispatched(AssetUploaded::class, function ($event) use ($asset) {
            return $event->asset = $asset;
        });
        Event::assertDispatched(AssetSaved::class);
    }

    /** @test */
    public function it_appends_timestamp_to_uploaded_files_filename_if_it_already_exists()
    {
        Event::fake();
        Carbon::setTestNow(Carbon::createFromTimestamp(1549914700));
        $asset = $this->container->makeAsset('path/to/asset.jpg');
        Facades\AssetContainer::shouldReceive('findByHandle')->with('test_container')->andReturn($this->container);
        Storage::disk('test')->put('path/to/asset.jpg', '');
        Storage::disk('test')->assertExists('path/to/asset.jpg');

        $asset->upload(UploadedFile::fake()->image('asset.jpg'));

        Storage::disk('test')->assertExists('path/to/asset-1549914700.jpg');
        $this->assertEquals('path/to/asset-1549914700.jpg', $asset->path());
        Event::assertDispatched(AssetUploaded::class, function ($event) use ($asset) {
            return $event->asset = $asset;
        });
    }

    /** @test */
    public function it_gets_the_url_when_the_container_has_a_relative_url()
    {
        $container = $this->mock(AssetContainer::class);
        $container->shouldReceive('private')->andReturnFalse();
        $container->shouldReceive('url')->andReturn('/container');
        $asset = (new Asset)->container($container)->path('path/to/test.txt');

        $this->assertEquals('/container/path/to/test.txt', $asset->url());
        $this->assertEquals('/container/path/to/test.txt', (string) $asset);
    }

    /** @test */
    public function it_gets_the_url_when_the_container_has_an_absolute_url()
    {
        $container = $this->mock(AssetContainer::class);
        $container->shouldReceive('private')->andReturnFalse();
        $container->shouldReceive('url')->andReturn('http://example.com/container');
        $asset = (new Asset)->container($container)->path('path/to/test.txt');

        $this->assertEquals('http://example.com/container/path/to/test.txt', $asset->url());
        $this->assertEquals('http://example.com/container/path/to/test.txt', (string) $asset);
    }

    /** @test */
    public function it_gets_the_absolute_url()
    {
        $container = $this->mock(AssetContainer::class);
        $container->shouldReceive('private')->andReturnFalse();
        $container->shouldReceive('absoluteUrl')->andReturn('http://example.com');
        $asset = (new Asset)->container($container)->path('path/to/test.txt');

        $this->assertEquals('http://example.com/path/to/test.txt', $asset->absoluteUrl());
    }

    /** @test */
    public function there_is_no_url_for_a_private_asset()
    {
        $container = $this->mock(AssetContainer::class);
        $container->shouldReceive('id')->andReturn('container-id');
        $container->shouldReceive('private')->andReturnTrue();
        $asset = (new Asset)->container($container)->path('path/to/test.txt');

        $this->assertNull($asset->url());
        $this->assertNull($asset->absoluteUrl());
        $this->assertEquals('container-id::path/to/test.txt', (string) $asset);
    }

    private function toArrayKeysWhenFileExists()
    {
        return [
            'size', 'size_bytes', 'size_kilobytes', 'size_megabytes', 'size_gigabytes',
            'size_b', 'size_kb', 'size_mb', 'size_gb',
            'last_modified', 'last_modified_timestamp', 'last_modified_instance',
            'focus', 'focus_css', 'mime_type',
        ];
    }
}

<?php

namespace Tests\Assets;

use Carbon\Carbon;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Storage;
use Statamic\Assets\Asset;
use Statamic\Assets\AssetContainer;
use Statamic\Events\Data\AssetSaved;
use Statamic\Events\Data\AssetUploaded;
use Statamic\Facades;
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
    public function it_gets_the_extension()
    {
        $this->assertEquals('jpg', (new Asset)->path('asset.jpg')->extension());
        $this->assertEquals('txt', (new Asset)->path('asset.txt')->extension());
        $this->assertNull((new Asset)->path('asset')->extension());
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
    public function it_gets_meta_data()
    {
        Storage::fake('test');
        Storage::disk('test')->put('foo/test.txt', '');
        Storage::disk('test')->put('foo/.meta/test.txt.yaml', YAML::dump($meta = [
            'data' => ['foo' => 'bar'],
            'size' => 123,
        ]));

        $container = Facades\AssetContainer::make('test')->disk('test');
        $asset = (new Asset)->container($container)->path('foo/test.txt');

        $this->assertEquals($meta, $asset->meta());
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
        $asset = (new Asset)->container($container)->path('foo/image.jpg')->set('foo', 'bar');

        $this->assertEquals([
            'data' => ['foo' => 'bar'],
            'size' => 723,
            'last_modified' => Carbon::parse('2012-01-02 4:57pm')->timestamp,
            'width' => 30,
            'height' => 60,
        ], $asset->meta());
        Storage::disk('test')->assertExists('foo/.meta/image.jpg.yaml');
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
    public function it_deletes()
    {
        Storage::fake('local');
        $disk = Storage::disk('local');
        $disk->put('path/to/asset.txt', '');
        $container = Facades\AssetContainer::make('test')->disk('local');
        Facades\AssetContainer::shouldReceive('save')->with($container);
        $asset = (new Asset)->container($container)->path('path/to/asset.txt');
        $disk->assertExists('path/to/asset.txt');

        $return = $asset->delete();

        $this->assertEquals($asset, $return);
        $disk->assertMissing('path/to/asset.txt');

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
        $asset = (new Asset)->container($container)->path('old/asset.txt')->data(['foo' => 'bar']);
        $asset->save();
        $disk->assertExists('old/asset.txt');
        $disk->assertExists('old/.meta/asset.txt.yaml');
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
        $this->assertEquals([
            'new/asset.txt' => ['foo' => 'bar'],
        ], $container->assets('/', true)->keyBy->path()->map(function ($item) {
            return $item->data()->all();
        })->all());
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
        $asset = (new Asset)->container($container)->path('old/asset.txt')->data(['foo' => 'bar']);
        $asset->save();
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
        $this->assertEquals([
            'new/newfilename.txt' => ['foo' => 'bar'],
        ], $container->assets('/', true)->keyBy->path()->map(function ($item) {
            return $item->data()->all();
        })->all());
    }

    /** @test */
    public function it_renames()
    {
        Event::fake();
        $disk = Storage::fake('local');
        $disk->put('old/asset.txt', 'The asset contents');
        $container = Facades\AssetContainer::make('test')->disk('local');
        Facades\AssetContainer::shouldReceive('save')->with($container);
        $asset = (new Asset)->container($container)->path('old/asset.txt')->data(['foo' => 'bar']);
        $asset->save();
        $disk->assertExists('old/asset.txt');
        $disk->assertExists('old/.meta/asset.txt.yaml');
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
        $this->assertEquals([
            'old/newfilename.txt' => ['foo' => 'bar'],
        ], $container->assets('/', true)->keyBy->path()->map(function ($item) {
            return $item->data()->all();
        })->all());
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
    public function it_can_upload_a_file()
    {
        Event::fake();
        $asset = (new Asset)->container($this->container)->path('path/to/asset.jpg');
        Storage::disk('test')->assertMissing('path/to/asset.jpg');

        $return = $asset->upload(UploadedFile::fake()->image('asset.jpg'));

        $this->assertEquals($asset, $return);
        Storage::disk('test')->assertExists('path/to/asset.jpg');
        $this->assertEquals('path/to/asset.jpg', $asset->path());
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
        $asset = (new Asset)->container($this->container)->path('path/to/asset.jpg');
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
            'focus_css',
        ];
    }
}

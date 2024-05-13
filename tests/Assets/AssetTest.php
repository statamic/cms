<?php

namespace Tests\Assets;

use BadMethodCallException;
use Carbon\Carbon;
use Facades\Statamic\Fields\BlueprintRepository;
use Facades\Statamic\Imaging\ImageValidator;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Storage;
use Mockery;
use ReflectionClass;
use Statamic\Assets\Asset;
use Statamic\Assets\AssetContainer;
use Statamic\Assets\PendingMeta;
use Statamic\Assets\ReplacementFile;
use Statamic\Events\AssetCreated;
use Statamic\Events\AssetCreating;
use Statamic\Events\AssetDeleted;
use Statamic\Events\AssetDeleting;
use Statamic\Events\AssetReplaced;
use Statamic\Events\AssetReuploaded;
use Statamic\Events\AssetSaved;
use Statamic\Events\AssetSaving;
use Statamic\Events\AssetUploaded;
use Statamic\Exceptions\FileExtensionMismatch;
use Statamic\Facades;
use Statamic\Facades\Antlers;
use Statamic\Facades\File;
use Statamic\Facades\YAML;
use Statamic\Fields\Blueprint;
use Statamic\Fields\Fieldtype;
use Statamic\Fields\Value;
use Statamic\Fieldtypes\Assets\DimensionsRule;
use Statamic\Support\Arr;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Tests\PreventSavingStacheItemsToDisk;
use Tests\TestCase;

class AssetTest extends TestCase
{
    use PreventSavingStacheItemsToDisk;

    private $container;

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
        Storage::fake('attributes-cache');
    }

    /** @test */
    public function it_gets_data_values()
    {
        Storage::disk('test')->put('foo/test.txt', '');
        Storage::disk('test')->put('foo/.meta/test.txt.yaml', YAML::dump([
            'data' => [
                'one' => 'foo',
            ],
            'size' => 123,
        ]));
        $asset = (new Asset)->container($this->container)->path('foo/test.txt');

        // Ensure nothing is hydrated to the asset's data yet
        $asset->withoutHydrating(function ($asset) {
            $this->assertNull($asset->get('one'));
        });

        $this->assertEquals('foo', $asset->get('one'));
        $this->assertEquals('fallback', $asset->get('unknown', 'fallback'));
        $this->assertEquals(123, $asset->getRawMeta()['size']);
    }

    /** @test */
    public function it_gets_all_data_at_once()
    {
        Storage::disk('test')->put('foo/test.txt', '');
        Storage::disk('test')->put('foo/.meta/test.txt.yaml', YAML::dump([
            'data' => [
                'one' => 'foo',
                'two' => 'bar',
            ],
            'size' => 123,
        ]));
        $asset = (new Asset)->container($this->container)->path('foo/test.txt');

        // Ensure nothing is hydrated to the asset's data yet
        $asset->withoutHydrating(function ($asset) {
            $this->assertEquals([], $asset->data()->all());
        });

        $data = $asset->data()->all();

        $this->assertEquals(['one' => 'foo', 'two' => 'bar'], $data);
        $this->assertEquals(123, $asset->getRawMeta()['size']);
    }

    /** @test */
    public function it_sets_data_values()
    {
        Storage::disk('test')->put('foo/test.txt', '');
        Storage::disk('test')->put('foo/.meta/test.txt.yaml', YAML::dump([
            'data' => [
                'one' => 'foo',
                'two' => 'bar',
            ],
            'size' => 123,
        ]));
        $asset = (new Asset)->container($this->container)->path('foo/test.txt');

        // Ensure nothing is hydrated to the asset's data yet
        $asset->withoutHydrating(function ($asset) {
            $this->assertEquals([], $asset->data()->all());
        });

        $asset->set('one', 'new-foo');
        $return = $asset->set('three', 'qux');

        $this->assertEquals($asset, $return);

        // Assert data is correct without hydrating, to ensure the hydrate call happened when calling `set()` above
        $asset->withoutHydrating(function ($asset) {
            $this->assertEquals('new-foo', $asset->get('one'));
            $this->assertEquals('new-foo', Arr::get($asset->meta(), 'data.one'));
            $this->assertEquals('bar', $asset->get('two'));
            $this->assertEquals('bar', Arr::get($asset->meta(), 'data.two'));
            $this->assertEquals('qux', $asset->get('three'));
            $this->assertEquals('qux', Arr::get($asset->meta(), 'data.three'));
            $this->assertEquals('fallback', $asset->get('unknown', 'fallback'));
        });

        $this->assertEquals(123, $asset->getRawMeta()['size']);
    }

    /** @test */
    public function it_merges_data_values()
    {
        Storage::disk('test')->put('foo/test.txt', '');
        Storage::disk('test')->put('foo/.meta/test.txt.yaml', YAML::dump([
            'data' => [
                'one' => 'foo',
                'two' => 'bar',
            ],
            'size' => 123,
        ]));
        $asset = (new Asset)->container($this->container)->path('foo/test.txt');

        // Ensure nothing is hydrated to the asset's data yet
        $asset->withoutHydrating(function ($asset) {
            $this->assertEquals([], $asset->data()->all());
        });

        $return = $asset->merge([
            'one' => 'new-foo',
            'three' => 'qux',
        ]);

        $this->assertEquals($asset, $return);

        // Assert data is correct without hydrating, to ensure the hydrate call happened when calling `merge()` above
        $asset->withoutHydrating(function ($asset) {
            $this->assertEquals('new-foo', $asset->get('one'));
            $this->assertEquals('new-foo', Arr::get($asset->meta(), 'data.one'));
            $this->assertEquals('bar', $asset->get('two'));
            $this->assertEquals('bar', Arr::get($asset->meta(), 'data.two'));
            $this->assertEquals('qux', $asset->get('three'));
            $this->assertEquals('qux', Arr::get($asset->meta(), 'data.three'));
            $this->assertEquals('fallback', $asset->get('unknown', 'fallback'));
        });

        $this->assertEquals(123, $asset->getRawMeta()['size']);
    }

    /** @test */
    public function it_sets_all_data_at_once()
    {
        Storage::disk('test')->put('foo/test.txt', '');
        Storage::disk('test')->put('foo/.meta/test.txt.yaml', YAML::dump([
            'data' => [
                'one' => 'foo',
                'two' => 'bar',
            ],
            'size' => 123,
        ]));
        $asset = (new Asset)->container($this->container)->path('foo/test.txt');

        // Ensure nothing is hydrated to the asset's data yet
        $asset->withoutHydrating(function ($asset) {
            $this->assertEquals([], $asset->data()->all());
        });

        $return = $asset->data([
            'three' => 'baz',
            'four' => 'qux',
        ]);

        $this->assertEquals($asset, $return);

        // Assert data is correct without hydrating, to ensure the hydrate call happened when setting with `data()` above
        $asset->withoutHydrating(function ($asset) {
            $this->assertNull($asset->get('one'));
            $this->assertFalse(Arr::has($asset->meta(), 'data.one'));
            $this->assertNull($asset->get('two'));
            $this->assertFalse(Arr::has($asset->meta(), 'data.two'));
            $this->assertEquals('baz', $asset->get('three'));
            $this->assertEquals('baz', Arr::get($asset->meta(), 'data.three'));
            $this->assertEquals('qux', $asset->get('four'));
            $this->assertEquals('qux', Arr::get($asset->meta(), 'data.four'));
        });

        $this->assertEquals(123, $asset->getRawMeta()['size']);
    }

    /** @test */
    public function it_sets_data_values_using_magic_properties()
    {
        Storage::disk('test')->put('foo/test.txt', '');
        Storage::disk('test')->put('foo/.meta/test.txt.yaml', YAML::dump([
            'data' => [
                'one' => 'foo',
                'two' => 'bar',
            ],
            'size' => 123,
        ]));
        $asset = (new Asset)->container($this->container)->path('foo/test.txt');

        // Ensure nothing is hydrated to the asset's data yet
        $asset->withoutHydrating(function ($asset) {
            $this->assertEquals([], $asset->data()->all());
        });

        $asset->one = 'new-foo';
        $asset->three = 'qux';

        // Assert data is correct without hydrating, to ensure the hydrate call happened when setting magical property via `__set()`
        $asset->withoutHydrating(function ($asset) {
            $this->assertEquals('new-foo', $asset->get('one'));
            $this->assertEquals('new-foo', Arr::get($asset->meta(), 'data.one'));
            $this->assertEquals('bar', $asset->get('two'));
            $this->assertEquals('bar', Arr::get($asset->meta(), 'data.two'));
            $this->assertEquals('qux', $asset->get('three'));
            $this->assertEquals('qux', Arr::get($asset->meta(), 'data.three'));
            $this->assertEquals('fallback', $asset->get('unknown', 'fallback'));
        });

        $this->assertEquals(123, $asset->getRawMeta()['size']);
    }

    /** @test */
    public function it_removes_data_values()
    {
        Storage::disk('test')->put('foo/test.txt', '');
        Storage::disk('test')->put('foo/.meta/test.txt.yaml', YAML::dump([
            'data' => [
                'one' => 'foo',
                'two' => 'bar',
            ],
            'size' => 123,
        ]));
        $asset = (new Asset)->container($this->container)->path('foo/test.txt');

        // Ensure nothing is hydrated to the asset's data yet
        $asset->withoutHydrating(function ($asset) {
            $this->assertEquals([], $asset->data()->all());
        });

        // Calling remove should both hydrate and remove from the asset's data,
        // and this ensures that the removed key isn't re-added from the yaml
        $return = $asset->remove('one');

        $this->assertEquals($asset, $return);

        // Assert data is correct without hydrating, to ensure the hydrate call happened when calling `remove()` above
        $asset->withoutHydrating(function ($asset) {
            $this->assertNull($asset->get('one'));
            $this->assertFalse(Arr::has($asset->meta(), 'data.one'));
            $this->assertEquals('bar', $asset->get('two'));
            $this->assertEquals('bar', Arr::get($asset->meta(), 'data.two'));
        });

        $this->assertEquals(123, $asset->getRawMeta()['size']);
    }

    /**
     * @test
     *
     * @dataProvider reAddRemovedDataProvider
     **/
    public function it_doesnt_try_to_re_remove_newly_added_data_from_meta($reAddRemovedData)
    {
        Storage::disk('test')->put('foo/test.txt', '');
        Storage::disk('test')->put('foo/.meta/test.txt.yaml', YAML::dump([
            'data' => [
                'one' => 'foo',
                'two' => 'bar',
            ],
            'size' => 123,
        ]));
        $asset = (new Asset)->container($this->container)->path('foo/test.txt');

        // Calling `remove()` stores temporary `removedData` state on the asset to prevent it from getting
        // merged back into the meta. We want to ensure this state gets cleared when subsequently adding
        // new data, so that Statamic doesn't try to re-remove this key if it's intentionally re-added
        $return = $asset->remove('one');

        $this->assertEquals($asset, $return);
        $asset->withoutHydrating(function ($asset) {
            $this->assertNull($asset->get('one'));
            $this->assertFalse(Arr::has($asset->meta(), 'data.one'));
            $this->assertEquals('bar', $asset->get('two'));
            $this->assertEquals('bar', Arr::get($asset->meta(), 'data.two'));
        });
        $this->assertEquals(123, $asset->getRawMeta()['size']);

        // This is where `removedData` state should be removed
        $return = $reAddRemovedData($asset);

        // Assert that newly added data isn't affected by lingering `removedData` state
        $this->assertEquals($asset, $return);
        $asset->withoutHydrating(function ($asset) {
            $this->assertEquals('new-foo', $asset->get('one'));
            $this->assertEquals('new-foo', Arr::get($asset->meta(), 'data.one'));
            $this->assertEquals('bar', $asset->get('two'));
            $this->assertEquals('bar', Arr::get($asset->meta(), 'data.two'));
        });
        $this->assertEquals(123, $asset->getRawMeta()['size']);
    }

    public static function reAddRemovedDataProvider()
    {
        return [
            'by calling set method' => [fn ($asset) => $asset->set('one', 'new-foo')],
            'by calling data method' => [fn ($asset) => $asset->data(['one' => 'new-foo', 'two' => 'bar', 'three' => 'qux'])],
            'by calling merge method' => [fn ($asset) => $asset->merge(['one' => 'new-foo', 'three' => 'qux'])],
            'by calling __set() magically via property' => [function ($asset) {
                $asset->one = 'new-foo';

                return $asset;
            }],
        ];
    }

    /** @test */
    public function it_gets_evaluated_augmented_value_using_magic_property()
    {
        (new class extends Fieldtype
        {
            protected static $handle = 'test';

            public function augment($value)
            {
                return $value.' (augmented)';
            }
        })::register();

        $blueprint = Facades\Blueprint::makeFromFields(['charlie' => ['type' => 'test']]);
        BlueprintRepository::shouldReceive('find')->with('assets/test_container')->andReturn($blueprint);

        $asset = (new Asset)->container($this->container)->path('test.jpg');
        $asset->set('alfa', 'bravo');
        $asset->set('charlie', 'delta');

        $this->assertEquals('test.jpg', $asset->path);
        $this->assertEquals('test.jpg', $asset['path']);
        $this->assertEquals('bravo', $asset->alfa);
        $this->assertEquals('bravo', $asset['alfa']);
        $this->assertEquals('delta (augmented)', $asset->charlie);
        $this->assertEquals('delta (augmented)', $asset['charlie']);
    }

    /**
     * @test
     *
     * @dataProvider queryBuilderProvider
     **/
    public function it_has_magic_property_and_methods_for_fields_that_augment_to_query_builders($builder)
    {
        $builder->shouldReceive('get')->times(2)->andReturn('query builder results');
        app()->instance('mocked-builder', $builder);

        (new class extends Fieldtype
        {
            protected static $handle = 'test';

            public function augment($value)
            {
                return app('mocked-builder');
            }
        })::register();

        $blueprint = Facades\Blueprint::makeFromFields(['foo' => ['type' => 'test']]);
        BlueprintRepository::shouldReceive('find')->with('assets/test_container')->andReturn($blueprint);

        $asset = (new Asset)->path('test.txt')->container($this->container);
        $asset->set('foo', 'delta');

        $this->assertEquals('query builder results', $asset->foo);
        $this->assertEquals('query builder results', $asset['foo']);
        $this->assertSame($builder, $asset->foo());
    }

    public static function queryBuilderProvider()
    {
        return [
            'statamic' => [Mockery::mock(\Statamic\Query\Builder::class)],
            'database' => [Mockery::mock(\Illuminate\Database\Query\Builder::class)],
            'eloquent' => [Mockery::mock(\Illuminate\Database\Eloquent\Builder::class)],
        ];
    }

    /** @test */
    public function calling_unknown_method_throws_exception()
    {
        $this->expectException(BadMethodCallException::class);
        $this->expectExceptionMessage('Call to undefined method Statamic\Assets\Asset::thisFieldDoesntExist()');

        (new Asset)->path('test.txt')->container($this->container)->thisFieldDoesntExist();
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
        Storage::disk('test')->put('foo.mp4a', '');
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
            Storage::disk('test')->path('test.txt'),
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
    public function it_properly_merges_new_unsaved_data_to_meta()
    {
        Storage::fake('test');
        Storage::disk('test')->put('foo/test.txt', '');
        Storage::disk('test')->put('foo/.meta/test.txt.yaml', YAML::dump($expectedBeforeMerge = [
            'data' => ['one' => 'foo'],
            'size' => 123,
        ]));
        $container = Facades\AssetContainer::make('test')->disk('test');
        $asset = (new Asset)->container($container)->path('foo/test.txt');
        Facades\Asset::partialMock()->shouldReceive('save')->with($asset);
        $asset->save();

        $this->assertEquals($expectedBeforeMerge, $asset->meta());

        $asset->merge([
            'two' => 'bar',
            'three' => 'baz',
        ]);

        $expectedAfterMerge = [
            'data' => [
                'one' => 'foo',
                'two' => 'bar',
                'three' => 'baz',
            ],
            'size' => 123,
        ];

        $this->assertEquals($expectedAfterMerge, $asset->meta());
    }

    /** @test */
    public function it_does_not_write_to_meta_file_when_asset_does_not_exist()
    {
        Storage::fake('test');

        $container = Facades\AssetContainer::make('test')->disk('test');
        $asset = (new Asset)->container($container)->path('foo/test.txt');

        // No meta file should exist yet...
        $this->assertFalse(Storage::disk('test')->exists('foo/.meta/test.txt.yaml'));

        // Calling `meta` should return an empty meta array, but not write a meta file...
        $meta = $asset->meta();
        $this->assertEquals(['data' => []], $meta);
        $this->assertFalse(Storage::disk('test')->exists('foo/.meta/test.txt.yaml'));
    }

    /** @test */
    public function it_gets_meta_path()
    {
        $asset = (new Asset)->container($this->container)->path('test.txt');
        $this->assertEquals('.meta/test.txt.yaml', $asset->metaPath());

        $asset = (new Asset)->container($this->container)->path('foo/test.txt');
        $this->assertEquals('foo/.meta/test.txt.yaml', $asset->metaPath());
    }

    /** @test */
    public function it_generates_meta_on_demand_if_it_doesnt_exist()
    {
        Storage::fake('test');
        Carbon::setTestNow(Carbon::parse('2012-01-02 5:00pm'));

        $file = UploadedFile::fake()->image('image.jpg', 30, 60); // creates a 723 byte image
        Storage::disk('test')->putFileAs('foo', $file, 'image.jpg');
        $realFilePath = Storage::disk('test')->path('foo/image.jpg');
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
            'duration' => null,
        ];

        $metaWithData = [
            'data' => ['foo' => 'bar'],
            'size' => 723,
            'last_modified' => Carbon::parse('2012-01-02 4:57pm')->timestamp,
            'width' => 30,
            'height' => 60,
            'mime_type' => 'image/jpeg',
            'duration' => null,
        ];

        // The meta that's saved to file will also be cached, but will not include in-memory data...
        $this->assertEquals($metaWithoutData, YAML::parse(Storage::disk('test')->get('foo/.meta/image.jpg.yaml')));
        $this->assertEquals($metaWithoutData, Cache::get($asset->metaCacheKey()));

        // Even though the meta data is not cached, we're still able able to get it off the intance...
        $this->assertEquals($metaWithData, $asset->meta());
        $this->assertEquals($metaWithoutData, Cache::get($asset->metaCacheKey()));

        // Saving should clear the cache and persist the new meta data to the filesystem...
        $asset->save();
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
        $realFilePath = Storage::disk('test')->path('foo/image.jpg');
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
            'duration' => null,
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
        Facades\Asset::partialMock()->shouldReceive('save')->with($asset);

        $return = $asset->save();

        $this->assertTrue($return);

        Event::assertDispatched(AssetSaving::class, function ($event) use ($asset) {
            return $event->asset = $asset;
        });

        Event::assertDispatched(AssetSaved::class, function ($event) use ($asset) {
            return $event->asset = $asset;
        });

        Event::assertDispatched(AssetCreating::class, function ($event) use ($asset) {
            return $event->asset = $asset;
        });

        Event::assertDispatched(AssetCreated::class, function ($event) use ($asset) {
            return $event->asset = $asset;
        });
        // Assertion about the meta file is in the AssetRepository test
    }

    /** @test */
    public function it_doesnt_save_when_asset_saving_event_returns_false()
    {
        Event::fake([AssetSaved::class]);
        Storage::fake('test');
        $container = Facades\AssetContainer::make('test')->disk('test');
        $asset = (new Asset)->container($container)->path('foo.jpg');
        Facades\Asset::partialMock()->shouldReceive('save')->with($asset);

        Event::listen(AssetSaving::class, function ($event) {
            return false;
        });

        $return = $asset->save();

        $this->assertFalse($return);

        Event::assertNotDispatched(AssetSaved::class, function ($event) use ($asset) {
            return $event->asset = $asset;
        });
    }

    /** @test */
    public function it_saves_quietly()
    {
        Event::fake();
        Storage::fake('test');
        $container = Facades\AssetContainer::make('test')->disk('test');
        $asset = (new Asset)->container($container)->path('foo.jpg');
        Facades\Asset::partialMock()->shouldReceive('save')->with($asset);

        $return = $asset->saveQuietly();

        $this->assertTrue($return);

        Event::assertNotDispatched(AssetSaved::class);
        Event::assertNotDispatched(AssetSaving::class);
    }

    /** @test */
    public function when_saving_quietly_the_cached_assets_withEvents_flag_will_be_set_back_to_true()
    {
        Event::fake();
        Storage::fake('test');
        $container = Facades\AssetContainer::make('test')->disk('test');
        $asset = (new Asset)->container($container)->path('foo.jpg');
        Facades\Asset::partialMock()->shouldReceive('save')->with($asset);

        $return = $asset->saveQuietly();

        $this->assertTrue($return);

        $reflection = new ReflectionClass($asset);
        $property = $reflection->getProperty('withEvents');
        $property->setAccessible(true);
        $withEvents = $property->getValue($asset);
        $this->assertTrue($withEvents);
    }

    /** @test */
    public function it_doesnt_add_path_to_container_listing_if_it_doesnt_exist()
    {
        Facades\AssetContainer::shouldReceive('findByHandle')->with('test_container')->andReturn($this->container);

        $this->container->makeAsset('one/two/foo.jpg')->save();

        $this->assertEquals([], $this->container->contents()->cached()->keys()->all());
    }

    /** @test */
    public function it_deletes()
    {
        Event::fake();
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
        Event::assertDispatched(AssetDeleting::class);
        Event::assertDispatched(AssetDeleted::class);
    }

    /** @test */
    public function it_deletes_quietly()
    {
        Event::fake();
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

        $return = $asset->deleteQuietly();

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
        Event::assertNotDispatched(AssetDeleting::class);
        Event::assertNotDispatched(AssetDeleted::class);
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
    public function it_lowercases_when_moving_to_another_folder_with_a_new_filename()
    {
        Storage::fake('local');
        $disk = Storage::disk('local');
        $disk->put('old/asset.txt', 'The asset contents');
        $container = Facades\AssetContainer::make('test')->disk('local');
        Facades\AssetContainer::shouldReceive('save')->with($container);
        Facades\AssetContainer::shouldReceive('findByHandle')->with('test')->andReturn($container);
        $asset = $container->makeAsset('old/asset.txt');
        $asset->save();

        $return = $asset->move('new', 'lowercase-THIS-file');

        $disk->assertExists('new/lowercase-this-file.txt');
        $disk->assertExists('new/.meta/lowercase-this-file.txt.yaml');
        $this->assertEquals([
            'new/lowercase-this-file.txt',
        ], $container->assets('/', true)->map->path()->all());
    }

    /** @test */
    public function it_doesnt_lowercase_moved_files_when_configured()
    {
        config(['statamic.assets.lowercase' => false]);

        Storage::fake('local');
        $disk = Storage::disk('local');
        $disk->put('old/asset.txt', 'The asset contents');
        $container = Facades\AssetContainer::make('test')->disk('local');
        Facades\AssetContainer::shouldReceive('save')->with($container);
        Facades\AssetContainer::shouldReceive('findByHandle')->with('test')->andReturn($container);
        $asset = $container->makeAsset('old/asset.txt');
        $asset->save();

        $return = $asset->move('new', 'do-NOT-lowercase-THIS-file');

        $disk->assertExists('new/do-NOT-lowercase-THIS-file.txt');
        $disk->assertExists('new/.meta/do-NOT-lowercase-THIS-file.txt.yaml');
        $this->assertEquals([
            'new/do-NOT-lowercase-THIS-file.txt',
        ], $container->assets('/', true)->map->path()->all());
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
    public function it_lowercases_when_renaming_by_default()
    {
        Event::fake();
        $disk = Storage::fake('local');
        $disk->put('old/asset.txt', 'The asset contents');
        $container = Facades\AssetContainer::make('test')->disk('local');
        Facades\AssetContainer::shouldReceive('save')->with($container);
        Facades\AssetContainer::shouldReceive('findByHandle')->with('test')->andReturn($container);
        $asset = $container->makeAsset('old/asset.txt');
        $asset->save();

        $return = $asset->rename('lowercase-THIS-file');

        $disk->assertExists('old/lowercase-this-file.txt');
        $disk->assertExists('old/.meta/lowercase-this-file.txt.yaml');
        $this->assertEquals([
            'old/lowercase-this-file.txt',
        ], $container->files()->all());
        $this->assertEquals([
            'old/lowercase-this-file.txt',
        ], $container->assets('/', true)->map->path()->all());
        $this->assertEquals([
            'old',
            'old/lowercase-this-file.txt',
        ], $container->contents()->cached()->keys()->all());
        Event::assertDispatched(AssetSaved::class);
    }

    /** @test */
    public function it_doesnt_lowercase_renamed_files_when_configured()
    {
        config(['statamic.assets.lowercase' => false]);

        Event::fake();
        $disk = Storage::fake('local');
        $disk->put('old/asset.txt', 'The asset contents');
        $container = Facades\AssetContainer::make('test')->disk('local');
        Facades\AssetContainer::shouldReceive('save')->with($container);
        Facades\AssetContainer::shouldReceive('findByHandle')->with('test')->andReturn($container);
        $asset = $container->makeAsset('old/asset.txt');
        $asset->save();

        $return = $asset->rename('do-NOT-lowercase-THIS-file');

        $disk->assertExists('old/do-NOT-lowercase-THIS-file.txt');
        $disk->assertExists('old/.meta/do-NOT-lowercase-THIS-file.txt.yaml');
        $this->assertEquals([
            'old/do-NOT-lowercase-THIS-file.txt',
        ], $container->files()->all());
        $this->assertEquals([
            'old/do-NOT-lowercase-THIS-file.txt',
        ], $container->assets('/', true)->map->path()->all());
        $this->assertEquals([
            'old',
            'old/do-NOT-lowercase-THIS-file.txt',
        ], $container->contents()->cached()->keys()->all());
        Event::assertDispatched(AssetSaved::class);
    }

    /** @test */
    public function it_bulk_renames()
    {
        Event::fake();
        $disk = Storage::fake('local');
        $container = Facades\AssetContainer::make('test')->disk('local');
        Facades\AssetContainer::shouldReceive('save')->with($container);
        Facades\AssetContainer::shouldReceive('findByHandle')->with('test')->andReturn($container);

        foreach (['foo', 'bar', 'baz', 'fraz', 'do-not-touch'] as $filename) {
            $disk->put("old/{$filename}.txt", 'The asset con ents');
            $asset = $container->makeAsset("old/{$filename}.txt")->data(['test' => $filename]);
            $asset->saveQuietly();
            $oldMeta[$filename] = $disk->get("old/.meta/{$filename}.txt.yaml");
            $disk->assertExists("old/{$filename}.txt");
            $disk->assertExists("old/.meta/{$filename}.txt.yaml");
        }
        $this->assertEquals([
            'old/foo.txt',
            'old/bar.txt',
            'old/baz.txt',
            'old/fraz.txt',
            'old/do-not-touch.txt',
        ], $container->files()->all());
        $this->assertEquals([
            'old/foo.txt' => ['test' => 'foo'],
            'old/bar.txt' => ['test' => 'bar'],
            'old/baz.txt' => ['test' => 'baz'],
            'old/fraz.txt' => ['test' => 'fraz'],
            'old/do-not-touch.txt' => ['test' => 'do-not-touch'],
        ], $container->assets('/', true)->keyBy->path()->map(function ($item) {
            return $item->data()->all();
        })->all());

        $assets = $container->assets()
            ->filter(function ($asset) {
                return $asset->filename() !== 'do-not-touch';
            })
            ->each->rename('tokyo', true); // <-- This is how our RenameAsset action bulk renames.

        $expected = [
            'foo' => 'tokyo',
            'bar' => 'tokyo-1',
            'baz' => 'tokyo-2',
            'fraz' => 'tokyo-3',
            'do-not-touch' => 'do-not-touch',
        ];

        Event::assertDispatched(AssetSaved::class, 4);
        foreach (['foo', 'bar', 'baz', 'fraz'] as $filename) {
            $disk->assertMissing("old/{$filename}.txt");
            $disk->assertMissing("old/.meta/{$filename}.txt.yaml");
        }
        foreach (array_values($expected) as $filename) {
            $disk->assertExists("old/{$filename}.txt");
            $disk->assertExists("old/.meta/{$filename}.txt.yaml");
        }
        foreach ($expected as $oldFilename => $newFilename) {
            $this->assertEquals($oldMeta[$oldFilename], $disk->get("old/.meta/{$newFilename}.txt.yaml"));
        }
        $this->assertEquals([
            'old/do-not-touch.txt',
            'old/tokyo.txt',
            'old/tokyo-1.txt',
            'old/tokyo-2.txt',
            'old/tokyo-3.txt',
        ], $container->files()->all());
        $this->assertEquals([
            'old/tokyo.txt' => ['test' => 'foo'],
            'old/tokyo-1.txt' => ['test' => 'bar'],
            'old/tokyo-2.txt' => ['test' => 'baz'],
            'old/tokyo-3.txt' => ['test' => 'fraz'],
            'old/do-not-touch.txt' => ['test' => 'do-not-touch'],
        ], $container->assets('/', true)->keyBy->path()->map(function ($item) {
            return $item->data()->all();
        })->all());
        $this->assertEquals([
            'old',
            'old/do-not-touch.txt',
            'old/tokyo.txt',
            'old/tokyo-1.txt',
            'old/tokyo-2.txt',
            'old/tokyo-3.txt',
        ], $container->contents()->cached()->keys()->all());
    }

    /** @test */
    public function it_replaces()
    {
        $this->fakeEventWithMacros();
        $disk = Storage::fake('local');
        $disk->put('some/old-asset.txt', 'Old asset contents');
        $disk->put('some/new-asset.txt', 'New asset contents');
        $container = Facades\AssetContainer::make('test')->disk('local');
        Facades\AssetContainer::shouldReceive('save')->with($container);
        Facades\AssetContainer::shouldReceive('findByHandle')->with('test')->andReturn($container);
        $oldAsset = tap($container->makeAsset('some/old-asset.txt')->data(['foo' => 'bar']))->saveQuietly();
        $newAsset = tap($container->makeAsset('some/new-asset.txt')->data(['foo' => 'baz']))->saveQuietly();
        $oldMeta = $disk->get('some/.meta/old-asset.txt.yaml');
        $newMeta = $disk->get('some/.meta/new-asset.txt.yaml');
        $disk->assertExists('some/old-asset.txt');
        $disk->assertExists('some/.meta/old-asset.txt.yaml');
        $disk->assertExists('some/new-asset.txt');
        $disk->assertExists('some/.meta/new-asset.txt.yaml');
        $this->assertEquals([
            'some/new-asset.txt',
            'some/old-asset.txt',
        ], $container->files()->all());
        $this->assertEquals([
            'some/old-asset.txt' => ['foo' => 'bar'],
            'some/new-asset.txt' => ['foo' => 'baz'],
        ], $container->assets('/', true)->keyBy->path()->map(function ($item) {
            return $item->data()->all();
        })->all());

        $return = $newAsset->replace($oldAsset);

        Event::assertDispatched(AssetDeleted::class, 0); // by default, the original asset is not deleted
        Event::assertDispatched(AssetSaved::class, 0); // by default, the new asset is not renamed
        Event::assertDispatched(AssetReplaced::class, 1); // our `UpdateAssetReferencesTest` covers what happens _after_ an asset is replaced

        $this->assertEquals($newAsset, $return);
        $disk->assertExists('some/old-asset.txt');
        $disk->assertExists('some/.meta/old-asset.txt.yaml');
        $disk->assertExists('some/new-asset.txt');
        $disk->assertExists('some/.meta/new-asset.txt.yaml');
        $this->assertEquals('Old asset contents', $disk->get('some/old-asset.txt'));
        $this->assertEquals('New asset contents', $disk->get('some/new-asset.txt'));
        $this->assertEquals([
            'some/new-asset.txt',
            'some/old-asset.txt',
        ], $container->files()->all());
        $this->assertEquals([
            'some/old-asset.txt' => ['foo' => 'bar'],
            'some/new-asset.txt' => ['foo' => 'baz'],
        ], $container->assets('/', true)->keyBy->path()->map(function ($item) {
            return $item->data()->all();
        })->all());
    }

    /** @test */
    public function it_can_delete_original_asset_when_replacing()
    {
        $this->fakeEventWithMacros();
        $disk = Storage::fake('local');
        $disk->put('some/old-asset.txt', 'Old asset contents');
        $disk->put('some/new-asset.txt', 'New asset contents');
        $container = Facades\AssetContainer::make('test')->disk('local');
        Facades\AssetContainer::shouldReceive('save')->with($container);
        Facades\AssetContainer::shouldReceive('findByHandle')->with('test')->andReturn($container);
        $oldAsset = tap($container->makeAsset('some/old-asset.txt')->data(['foo' => 'bar']))->saveQuietly();
        $newAsset = tap($container->makeAsset('some/new-asset.txt')->data(['foo' => 'baz']))->saveQuietly();
        $oldMeta = $disk->get('some/.meta/old-asset.txt.yaml');
        $newMeta = $disk->get('some/.meta/new-asset.txt.yaml');
        $disk->assertExists('some/old-asset.txt');
        $disk->assertExists('some/.meta/old-asset.txt.yaml');
        $disk->assertExists('some/new-asset.txt');
        $disk->assertExists('some/.meta/new-asset.txt.yaml');
        $this->assertEquals([
            'some/new-asset.txt',
            'some/old-asset.txt',
        ], $container->files()->all());
        $this->assertEquals([
            'some/old-asset.txt' => ['foo' => 'bar'],
            'some/new-asset.txt' => ['foo' => 'baz'],
        ], $container->assets('/', true)->keyBy->path()->map(function ($item) {
            return $item->data()->all();
        })->all());

        // Replace with `$deleteOriginal = true`
        $return = $newAsset->replace($oldAsset, true);

        Event::assertDispatched(AssetDeleted::class, 1); // because we passed the flag, the original asset should be deleted
        Event::assertDispatched(AssetSaved::class, 0); // by default, the new asset is not renamed
        Event::assertDispatched(AssetReplaced::class, 1); // our `UpdateAssetReferencesTest` covers what happens _after_ an asset is replaced

        $this->assertEquals($newAsset, $return);
        $disk->assertMissing('some/old-asset.txt');
        $disk->assertMissing('some/.meta/old-asset.txt.yaml');
        $disk->assertExists('some/new-asset.txt');
        $disk->assertExists('some/.meta/new-asset.txt.yaml');
        $this->assertEquals('New asset contents', $disk->get('some/new-asset.txt'));
        $this->assertEquals([
            'some/new-asset.txt',
        ], $container->files()->all());
        $this->assertEquals([
            'some/new-asset.txt' => ['foo' => 'baz'],
        ], $container->assets('/', true)->keyBy->path()->map(function ($item) {
            return $item->data()->all();
        })->all());
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
    public function it_passes_the_dimensions_validation()
    {
        $file = UploadedFile::fake()->image('image.jpg', 30, 60);
        $validDimensions = (new DimensionsRule(['max_width=10']))->passes('Image', [$file]);

        $this->assertFalse($validDimensions);
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
    public function it_gets_no_ratio_when_height_is_zero()
    {
        Storage::fake('test');
        Storage::disk('test')->put('image.jpg', '');
        Storage::disk('test')->put('.meta/image.jpg.yaml', YAML::dump(['width' => '30', 'height' => '0']));

        $container = Facades\AssetContainer::make('test')->disk('test');

        $asset = (new Asset)->container($container)->path('image.jpg');

        $this->assertEquals([30, 0], $asset->dimensions());
        $this->assertEquals(30, $asset->width());
        $this->assertEquals(0, $asset->height());
        $this->assertEquals(null, $asset->ratio());
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
        $asset->shouldReceive('extension')->andReturn('txt');

        $asset->shouldReceive('meta')->times(0);

        $this->assertEquals([null, null], $asset->dimensions());
    }

    /** @test */
    public function it_gets_file_size_in_bytes()
    {
        $container = $this->container;
        $size = filesize($fixture = __DIR__.'/__fixtures__/container/a.txt');
        copy($fixture, Storage::disk('test')->path('test.txt'));

        $asset = (new Asset)
            ->container($this->container)
            ->path('test.txt');

        $this->assertEquals($size, $asset->size());
    }

    /** @test */
    public function it_gets_the_title()
    {
        $asset = (new Asset)
            ->path('path/to/asset.jpg')
            ->container($this->container);

        $this->assertEquals('asset.jpg', $asset->title());
        $this->assertEquals('asset.jpg', $asset->title);

        $asset->set('title', 'custom title');
        $this->assertEquals('custom title', $asset->title());
        $this->assertEquals('custom title', $asset->title);
    }

    /** @test */
    public function it_compiles_augmented_array_data()
    {
        Facades\Blueprint::shouldReceive('find')
            ->with('assets/test_container')
            ->andReturn($blueprint = (new Blueprint)->setHandle('test_container')->setNamespace('assets'));

        $asset = (new Asset)
            ->path('path/to/asset.jpg')
            ->container($this->container)
            ->setSupplement('foo', 'bar');

        $array = $asset->toAugmentedArray();

        $expectedValues = [
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
        ];
        foreach ($expectedValues as $k => $v) {
            $this->assertEquals($v, $array[$k]->value());
        }

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
            ->path('path/to/asset.jpg')
            ->container($this->container)
            ->set('title', 'test')
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

        $this->assertSame($asset->augmentedValue('focus_css')->value(), '75% 25%');
        $this->assertTrue($asset->augmentedValue('has_focus')->value());
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

        $this->assertSame($asset->augmentedValue('focus_css')->value(), '50% 50%');
        $this->assertFalse($asset->augmentedValue('has_focus')->value());
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

    /** @test */
    public function if_saving_event_returns_false_during_upload_the_asset_doesnt_save()
    {
        Event::fake([AssetSaved::class, AssetUploaded::class, AssetCreated::class]);

        Event::listen(AssetCreating::class, function ($event) {
            return false;
        });

        $asset = (new Asset)->container($this->container)->path('path/to/asset.jpg')->syncOriginal();

        Facades\AssetContainer::shouldReceive('findByHandle')->with('test_container')->andReturn($this->container);
        Storage::disk('test')->assertMissing('path/to/asset.jpg');

        $return = $asset->upload(UploadedFile::fake()->image('asset.jpg', 13, 15));

        $this->assertFalse($return);

        Storage::disk('test')->assertMissing('path/to/asset.jpg');

        Event::assertNotDispatched(AssetSaved::class);
        Event::assertNotDispatched(AssetUploaded::class);
        Event::assertNotDispatched(AssetCreated::class);
    }

    private function uploadFileTest()
    {
        Event::fake();
        $asset = (new Asset)->container($this->container)->path('path/to/asset.jpg')->syncOriginal();

        Facades\AssetContainer::shouldReceive('findByHandle')->with('test_container')->andReturn($this->container);
        Storage::disk('test')->assertMissing('path/to/asset.jpg');

        // This should only get called when glide processing source image on upload...
        ImageValidator::partialMock()->shouldReceive('isValidImage')->never();

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

        Event::assertDispatched(AssetCreating::class, fn ($event) => $event->asset === $asset);
        Event::assertDispatched(AssetSaved::class, fn ($event) => $event->asset === $asset);
        Event::assertDispatched(AssetUploaded::class, fn ($event) => $event->asset === $asset);
        Event::assertDispatched(AssetCreated::class, fn ($event) => $event->asset === $asset);
    }

    /** @test */
    public function it_can_upload_an_image_into_a_container_with_glide_config()
    {
        Event::fake();

        config(['statamic.assets.image_manipulation.presets.small' => [
            'w' => '15',
            'h' => '15',
        ]]);

        $this->container->sourcePreset('small');

        $asset = (new Asset)->container($this->container)->path('path/to/asset.jpg')->syncOriginal();

        Facades\AssetContainer::shouldReceive('findByHandle')->with('test_container')->andReturn($this->container);
        Storage::disk('test')->assertMissing('path/to/asset.jpg');

        ImageValidator::partialMock()
            ->shouldReceive('isValidImage')
            ->with('jpg', 'image/jpeg')
            ->andReturnTrue()
            ->once();

        $return = $asset->upload(UploadedFile::fake()->image('asset.jpg', 20, 30));

        $this->assertEquals($asset, $return);
        $this->assertDirectoryExists($glideDir = storage_path('statamic/glide/tmp'));
        $this->assertEmpty(app('files')->allFiles($glideDir)); // no temp files
        Storage::disk('test')->assertExists('path/to/asset.jpg');
        $this->assertEquals('path/to/asset.jpg', $asset->path());
        Event::assertDispatched(AssetUploaded::class, function ($event) use ($asset) {
            return $event->asset = $asset;
        });
        Event::assertDispatched(AssetSaved::class);
        $meta = $asset->meta();

        $this->assertEquals(10, $meta['width']);
        $this->assertEquals(15, $meta['height']);
    }

    public static function formatParamsProvider()
    {
        return [['format'], ['fm']];
    }

    /**
     * @test
     *
     * @dataProvider formatParamsProvider
     **/
    public function it_can_upload_an_image_into_a_container_with_new_extension_format($formatParam)
    {
        Event::fake();

        config(['statamic.assets.image_manipulation.presets.enforce_png' => [
            $formatParam => 'png',
        ]]);

        $this->container->sourcePreset('enforce_png');

        $asset = (new Asset)->container($this->container)->path('path/to/asset.jpg')->syncOriginal();

        Facades\AssetContainer::shouldReceive('findByHandle')->with('test_container')->andReturn($this->container);
        Storage::disk('test')->assertMissing('path/to/asset.jpg');

        ImageValidator::partialMock()
            ->shouldReceive('isValidImage')
            ->with('jpg', 'image/jpeg')
            ->andReturnTrue()
            ->once();

        $return = $asset->upload(UploadedFile::fake()->image('asset.jpg', 20, 30));

        $this->assertEquals($asset, $return);
        $this->assertDirectoryExists($glideDir = storage_path('statamic/glide/tmp'));
        $this->assertEmpty(app('files')->allFiles($glideDir)); // no temp files
        Storage::disk('test')->assertMissing('path/to/asset.jpg');
        Storage::disk('test')->assertExists('path/to/asset.png');
        $this->assertEquals('path/to/asset.png', $asset->path());
        Event::assertDispatched(AssetUploaded::class, function ($event) use ($asset) {
            return $event->asset = $asset;
        });
        Event::assertDispatched(AssetSaved::class);
    }

    /** @test */
    public function it_sanitizes_svgs_on_upload()
    {
        Event::fake();

        $asset = (new Asset)->container($this->container)->path('path/to/asset.svg')->syncOriginal();

        Facades\AssetContainer::shouldReceive('findByHandle')->with('test_container')->andReturn($this->container);
        Storage::disk('test')->assertMissing('path/to/asset.svg');

        $return = $asset->upload(UploadedFile::fake()->createWithContent('asset.svg', '<?xml version="1.0" encoding="UTF-8" standalone="no"?><svg xmlns="http://www.w3.org/2000/svg" width="500" height="500"><script type="text/javascript">alert(`Bad stuff could go in here.`);</script></svg>'));

        $this->assertEquals($asset, $return);
        Storage::disk('test')->assertExists('path/to/asset.svg');
        $this->assertEquals('path/to/asset.svg', $asset->path());

        // Ensure the inline scripts were stripped out.
        $this->assertStringNotContainsString('<script', $asset->contents());
        $this->assertStringNotContainsString('Bad stuff could go in here.', $asset->contents());
        $this->assertStringNotContainsString('</script>', $asset->contents());
    }

    /** @test */
    public function it_does_not_sanitizes_svgs_on_upload_when_behaviour_is_disabled()
    {
        Event::fake();

        config()->set('statamic.assets.svg_sanitization_on_upload', false);

        $asset = (new Asset)->container($this->container)->path('path/to/asset.svg')->syncOriginal();

        Facades\AssetContainer::shouldReceive('findByHandle')->with('test_container')->andReturn($this->container);
        Storage::disk('test')->assertMissing('path/to/asset.svg');

        $return = $asset->upload(UploadedFile::fake()->createWithContent('asset.svg', '<?xml version="1.0" encoding="UTF-8" standalone="no"?><svg xmlns="http://www.w3.org/2000/svg" width="500" height="500"><script type="text/javascript">alert(`Bad stuff could go in here.`);</script></svg>'));

        $this->assertEquals($asset, $return);
        Storage::disk('test')->assertExists('path/to/asset.svg');
        $this->assertEquals('path/to/asset.svg', $asset->path());

        // Ensure the inline scripts were stripped out.
        $this->assertStringContainsString('<script', $asset->contents());
        $this->assertStringContainsString('Bad stuff could go in here.', $asset->contents());
        $this->assertStringContainsString('</script>', $asset->contents());
    }

    public static function nonGlideableFileExtensionsProvider()
    {
        return [
            ['txt'], // not an image
            ['md'],  // not an image
            ['svg'], // doesn't work with imagick without extra server config
            ['pdf'], // doesn't work with imagick without extra server config
        ];
    }

    /**
     * @test
     *
     * @dataProvider nonGlideableFileExtensionsProvider
     **/
    public function it_doesnt_process_or_error_when_uploading_non_glideable_file_with_glide_config($extension)
    {
        Event::fake();

        config(['statamic.assets.image_manipulation.presets.small' => [
            'w' => '15',
            'h' => '15',
        ]]);

        $this->container->sourcePreset('small');

        $asset = (new Asset)->container($this->container)->path("path/to/file.{$extension}")->syncOriginal();

        Facades\AssetContainer::shouldReceive('findByHandle')->with('test_container')->andReturn($this->container);
        Storage::disk('test')->assertMissing("path/to/file.{$extension}");

        // Ensure a glide server is never instantiated for these extensions...
        Facades\Glide::partialMock()->shouldReceive('server')->never();

        $return = $asset->upload(UploadedFile::fake()->createWithContent("file.{$extension}", '<svg width="20" height="30"></svg>'));

        $this->assertEquals($asset, $return);
        $this->assertDirectoryExists($glideDir = storage_path('statamic/glide/tmp'));
        $this->assertEmpty(app('files')->allFiles($glideDir)); // no temp files
        Storage::disk('test')->assertExists("path/to/file.{$extension}");
        $this->assertEquals("path/to/file.{$extension}", $asset->path());
        Event::assertDispatched(AssetUploaded::class, function ($event) use ($asset) {
            return $event->asset = $asset;
        });
        Event::assertDispatched(AssetSaved::class);
    }

    /** @test */
    public function it_can_process_a_custom_image_format()
    {
        Event::fake();

        config(['statamic.assets.image_manipulation.presets.small' => [
            'w' => '15',
            'h' => '15',
        ]]);

        // Normally pdf files (for example) are not supported by gd or imagick. However, imagick does
        // does actually support over 100 formats with extra configuration (eg. via ghostscript).
        // Thus, we allow the user to configure additional extensions in their assets config.
        config(['statamic.assets.image_manipulation.additional_extensions' => [
            'pdf',
        ]]);

        $this->container->sourcePreset('small');

        $asset = (new Asset)->container($this->container)->path('path/to/asset.pdf')->syncOriginal();

        Facades\AssetContainer::shouldReceive('findByHandle')->with('test_container')->andReturn($this->container);
        Storage::disk('test')->assertMissing('path/to/asset.pdf');

        $file = UploadedFile::fake()->image('asset.pdf', 20, 30);

        // Ensure a glide server is instantiated and `makeImage()` is called...
        Facades\Glide::partialMock()
            ->shouldReceive('server->makeImage')
            ->andReturn($file->getFilename())
            ->once();

        // Since we're mocking the glide server, and since the uploader's `write()` method expects
        // this location, we need to force it into that storage path for this test to pass...
        File::move($file->getRealPath(), $tempUploadedFilePath = storage_path('statamic/glide/tmp').'/'.$file->getFilename());

        // Perform the upload...
        $return = $asset->upload($file);

        // Now we'll delete that temporary UploadedFile, because we moved it into the app's storage above, and
        // it's normally not supposed to be there. This is necessary to prevent state issues across tests...
        File::delete($tempUploadedFilePath);

        $this->assertEquals($asset, $return);
        $this->assertDirectoryExists($glideDir = storage_path('statamic/glide/tmp'));
        $this->assertEmpty(app('files')->allFiles($glideDir)); // no temp files
        Storage::disk('test')->assertExists('path/to/asset.pdf');
        $this->assertEquals('path/to/asset.pdf', $asset->path());
        Event::assertDispatched(AssetUploaded::class, function ($event) use ($asset) {
            return $event->asset = $asset;
        });
        Event::assertDispatched(AssetSaved::class);
        $meta = $asset->meta();

        // Normally we assert changes to the meta, but we cannot in this test because we can't guarantee
        // the test suite has imagick with ghostscript installed (required for pdf files, for example).
        // $this->assertEquals(10, $meta['width']);
        // $this->assertEquals(15, $meta['height']);
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
    public function it_lowercases_uploaded_filenames_by_default()
    {
        Event::fake();
        $asset = $this->container->makeAsset('path/to/lowercase-THIS-asset.JPG');
        Facades\AssetContainer::shouldReceive('findByHandle')->with('test_container')->andReturn($this->container);

        $asset->upload(UploadedFile::fake()->image('lowercase-THIS-asset.JPG'));

        Storage::disk('test')->assertExists('path/to/lowercase-this-asset.jpg');
        $this->assertEquals('path/to/lowercase-this-asset.jpg', $asset->path());
        Event::assertDispatched(AssetUploaded::class, function ($event) use ($asset) {
            return $event->asset = $asset;
        });
    }

    /** @test */
    public function reuploading_will_replace_the_file_with_the_same_filename()
    {
        Event::fake();
        $asset = (new Asset)->container($this->container)->path('path/to/asset.jpg')->syncOriginal();

        Facades\AssetContainer::shouldReceive('findByHandle')->with('test_container')->andReturn($this->container);
        Storage::disk('test')->assertMissing('path/to/asset.jpg');

        $asset->upload(UploadedFile::fake()->image('asset.jpg', 13, 15));

        Storage::disk('test')->assertExists('path/to/asset.jpg');
        $originalFileContents = Storage::disk('test')->get('path/to/asset.jpg');
        $this->assertEquals('path/to/asset.jpg', $asset->path());
        $meta = $asset->meta();
        $this->assertEquals(13, $meta['width']);
        $this->assertEquals(15, $meta['height']);

        // Place an image in the filesystem that would have previously been uploaded,
        // most likely using the files fieldtype in the "replace asset" action modal.
        $uploadDisk = Storage::fake('local');
        UploadedFile::fake()->image('', 40, 25)->storeAs('path/to', 'different-filename.jpg', ['disk' => 'local']);
        $uploadDisk->assertExists('path/to/different-filename.jpg');

        $file = new ReplacementFile('path/to/different-filename.jpg');

        $return = $asset->reupload($file);

        $this->assertEquals($asset, $return);
        $this->assertEquals('path/to/asset.jpg', $asset->path());
        $meta = $asset->meta();
        $this->assertEquals(40, $meta['width']);
        $this->assertEquals(25, $meta['height']);
        Storage::disk('test')->assertExists('path/to/asset.jpg');
        Storage::disk('test')->assertMissing('path/to/different-filename.jpg');
        $this->assertNotEquals($originalFileContents, Storage::disk('test')->get('path/to/asset.jpg'));

        Event::assertDispatched(AssetUploaded::class, 1); // Once for the initial upload, but not again for the reupload.
        Event::assertDispatched(AssetReuploaded::class, function ($event) use ($asset) {
            return $event->asset->id() === $asset->id();
        });
        Event::assertDispatched(AssetSaved::class, 1); // Once during the initial upload, but not again for the reupload.

        // Assertions that the Glide cache is cleared and the presets
        // are regenerated for this asset are in ReuploadAssetTest.
    }

    /** @test */
    public function cannot_reupload_a_file_with_a_different_extension()
    {
        $this->expectException(FileExtensionMismatch::class);
        $this->expectExceptionMessage('The file extension must match the original file.');

        Event::fake();
        $asset = (new Asset)->container($this->container)->path('path/to/asset.jpg')->syncOriginal();
        Facades\AssetContainer::shouldReceive('findByHandle')->with('test_container')->andReturn($this->container);

        $replacementFile = new ReplacementFile('path/to/asset.png');

        $asset->reupload($replacementFile);

        Event::assertNotDispatched(AssetReuploaded::class);
        Event::assertNotDispatched(AssetSaved::class);
    }

    /** @test */
    public function it_doesnt_lowercase_uploaded_filenames_when_configured()
    {
        config(['statamic.assets.lowercase' => false]);

        Event::fake();
        $asset = $this->container->makeAsset('path/to/do-NOT-lowercase-THIS-asset.JPG');
        Facades\AssetContainer::shouldReceive('findByHandle')->with('test_container')->andReturn($this->container);

        $asset->upload(UploadedFile::fake()->image('do-NOT-lowercase-THIS-asset.JPG'));

        Storage::disk('test')->assertExists('path/to/do-NOT-lowercase-THIS-asset.JPG');
        $this->assertEquals('path/to/do-NOT-lowercase-THIS-asset.JPG', $asset->path());
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

    /** @test */
    public function it_sends_a_download_response()
    {
        Storage::disk('test')->put('test.txt', '');

        $asset = (new Asset)->container($this->container)->path('test.txt');

        $response = $asset->download();

        $this->assertInstanceOf(StreamedResponse::class, $response);
        $this->assertEquals('attachment; filename=test.txt', $response->headers->get('content-disposition'));
    }

    /** @test */
    public function it_sends_a_download_response_with_a_different_name_and_custom_headers()
    {
        Storage::disk('test')->put('test.txt', '');

        $asset = (new Asset)->container($this->container)->path('test.txt');

        $response = $asset->download('foo.txt', ['foo' => 'bar']);

        $this->assertInstanceOf(StreamedResponse::class, $response);
        $this->assertEquals('attachment; filename=foo.txt', $response->headers->get('content-disposition'));
        $this->assertArraySubset(['foo' => ['bar']], $response->headers->all());
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

    /** @test */
    public function it_converts_to_an_array()
    {
        $fieldtype = new class extends Fieldtype
        {
            protected static $handle = 'test';

            public function augment($value)
            {
                return [
                    new Value('alfa'),
                    new Value([
                        new Value('bravo'),
                        new Value('charlie'),
                        'delta',
                    ]),
                ];
            }
        };
        $fieldtype::register();

        $blueprint = Blueprint::makeFromFields([
            'baz' => [
                'type' => 'test',
            ],
        ]);
        BlueprintRepository::shouldReceive('find')->with('assets/test_container')->andReturn($blueprint);

        $asset = (new Asset)->container($this->container)->path('test.jpg');
        $asset->set('foo', 'bar');
        $asset->set('baz', 'qux');

        $this->assertInstanceOf(Arrayable::class, $asset);

        $array = $asset->toArray();
        $this->assertEquals($asset->augmented()->keys(), array_keys($array));
        $this->assertEquals([
            'alfa',
            [
                'bravo',
                'charlie',
                'delta',
            ],
        ], $array['baz'], 'Value objects are not resolved recursively');

        $array = $asset
            ->selectedQueryColumns($keys = ['id', 'foo', 'baz'])
            ->toArray();

        $this->assertEquals($keys, array_keys($array), 'toArray keys differ from selectedQueryColumns');
    }

    /** @test */
    public function only_requested_relationship_fields_are_included_in_to_array()
    {
        $regularFieldtype = new class extends Fieldtype
        {
            protected static $handle = 'regular';

            public function augment($value)
            {
                return 'augmented '.$value;
            }
        };
        $regularFieldtype::register();

        $relationshipFieldtype = new class extends Fieldtype
        {
            protected static $handle = 'relationship';
            protected $relationship = true;

            public function augment($values)
            {
                return collect($values)->map(fn ($value) => 'augmented '.$value)->all();
            }
        };
        $relationshipFieldtype::register();

        $blueprint = Blueprint::makeFromFields([
            'alfa' => ['type' => 'regular'],
            'bravo' => ['type' => 'relationship'],
            'charlie' => ['type' => 'relationship'],
        ]);
        BlueprintRepository::shouldReceive('find')->with('assets/test_container')->andReturn($blueprint);

        $asset = (new Asset)->container($this->container)->path('test.jpg');
        $asset->set('alfa', 'one');
        $asset->set('bravo', ['a', 'b']);
        $asset->set('charlie', ['c', 'd']);

        $this->assertEquals([
            'alfa' => 'augmented one',
            'bravo' => ['a', 'b'],
            'charlie' => ['augmented c', 'augmented d'],
        ], Arr::only($asset->selectedQueryRelations(['charlie'])->toArray(), ['alfa', 'bravo', 'charlie']));
    }

    /**
     * @test
     */
    public function it_has_a_dirty_state()
    {
        $container = Facades\AssetContainer::make('test')->disk('test');
        Facades\AssetContainer::shouldReceive('findByHandle')->with('test')->andReturn($container);

        $asset = (new Asset)->container($container)->path('test.jpg');

        $asset->data([
            'title' => 'English',
            'food' => 'Burger',
            'drink' => 'Water',
        ])->save();

        $this->assertFalse($asset->isDirty());
        $this->assertFalse($asset->isDirty('title'));
        $this->assertFalse($asset->isDirty('food'));
        $this->assertFalse($asset->isDirty(['title']));
        $this->assertFalse($asset->isDirty(['food']));
        $this->assertFalse($asset->isDirty(['title', 'food']));
        $this->assertTrue($asset->isClean());
        $this->assertTrue($asset->isClean('title'));
        $this->assertTrue($asset->isClean('food'));
        $this->assertTrue($asset->isClean(['title']));
        $this->assertTrue($asset->isClean(['food']));
        $this->assertTrue($asset->isClean(['title', 'food']));

        $asset->merge(['title' => 'French']);

        $this->assertTrue($asset->isDirty());
        $this->assertTrue($asset->isDirty('title'));
        $this->assertFalse($asset->isDirty('food'));
        $this->assertTrue($asset->isDirty(['title']));
        $this->assertFalse($asset->isDirty(['food']));
        $this->assertTrue($asset->isDirty(['title', 'food']));
        $this->assertFalse($asset->isClean());
        $this->assertFalse($asset->isClean('title'));
        $this->assertTrue($asset->isClean('food'));
        $this->assertFalse($asset->isClean(['title']));
        $this->assertTrue($asset->isClean(['food']));
        $this->assertFalse($asset->isClean(['title', 'food']));
    }

    /** @test */
    public function it_syncs_original_at_the_right_time()
    {
        $eventsHandled = 0;

        Event::listen(function (AssetSaved $event) use (&$eventsHandled) {
            $eventsHandled++;
            $this->assertTrue($event->asset->isDirty());
        });

        $container = Facades\AssetContainer::make('test')->disk('test');
        Facades\AssetContainer::shouldReceive('findByHandle')->with('test')->andReturn($container);
        $asset = $container->makeAsset('test.jpg');

        $asset
            ->set('foo', 'bar')
            ->save();

        $this->assertFalse($asset->isDirty());
        $this->assertEquals(1, $eventsHandled);
    }

    /** @test */
    public function it_augments_in_the_parser()
    {
        $container = Mockery::mock($this->container)->makePartial();
        $container->shouldReceive('private')->andReturnFalse();
        $container->shouldReceive('url')->andReturn('/container');
        $asset = (new Asset)->container($container)->path('path/to/test.txt');

        $this->assertEquals('/container/path/to/test.txt', Antlers::parse('{{ asset }}', ['asset' => $asset]));

        $this->assertEquals('path/to/test.txt', Antlers::parse('{{ asset }}{{ path }}{{ /asset }}', ['asset' => $asset]));

        $this->assertEquals('test.txt', Antlers::parse('{{ asset:basename }}', ['asset' => $asset]));

        // The "asset" Tag will output nothing when an invalid asset src is passed. It doesn't throw an exception.
        $this->assertEquals('', Antlers::parse('{{ asset src="invalid" }}{{ basename }}{{ /asset }}', ['asset' => $asset]));
    }

    /** @test */
    public function it_syncs_original_state_with_no_data()
    {
        $asset = (new Asset)->container($this->container)->path('path/to/test.txt');

        $this->assertEquals([], $asset->getRawOriginal());

        $asset->syncOriginal();

        $this->assertEquals([
            'path' => 'path/to/test.txt',
            'data' => new PendingMeta('data'),
        ], $asset->getRawOriginal());

        $this->assertEquals([
            'path' => 'path/to/test.txt',
            'data' => [],
        ], $asset->getOriginal());

        // Test that the pending meta was resolved
        $this->assertSame($asset->getOriginal(), $asset->getRawOriginal());

        Storage::disk('test')->assertMissing('path/to/.meta/test.txt.yaml');
    }

    /** @test */
    public function it_syncs_original_state_with_no_data_but_with_data_in_meta()
    {
        Storage::disk('test')->put('path/to/test.txt', '');
        Storage::disk('test')->put('path/to/.meta/test.txt.yaml', "data:\n  foo: bar");
        $asset = (new Asset)->container($this->container)->path('path/to/test.txt');

        $this->assertEquals([], $asset->getRawOriginal());

        $asset->syncOriginal();

        $this->assertEquals([
            'path' => 'path/to/test.txt',
            'data' => new PendingMeta('data'),
        ], $asset->getRawOriginal());

        $this->assertEquals([
            'path' => 'path/to/test.txt',
            'data' => [
                'foo' => 'bar',
            ],
        ], $asset->getOriginal());

        // Test that the pending meta was resolved
        $this->assertSame($asset->getOriginal(), $asset->getRawOriginal());

        Storage::disk('test')->assertExists('path/to/.meta/test.txt.yaml');
    }

    /** @test */
    public function it_syncs_original_state_with_data()
    {
        Storage::disk('test')->put('path/to/test.txt', '');
        $yaml = <<<'YAML'
data:
  alfa: bravo
  charlie: delta
  echo: foxtrot
YAML;
        Storage::disk('test')->put('path/to/.meta/test.txt.yaml', $yaml);

        $asset = (new Asset)
            ->container($this->container)
            ->path('path/to/test.txt')
            ->set('charlie', 'brown')
            ->remove('echo');

        $this->assertEquals([], $asset->getRawOriginal());

        $asset->syncOriginal();

        $this->assertEquals([
            'path' => 'path/to/test.txt',
            'data' => new PendingMeta('data'),
        ], $asset->getRawOriginal());

        $this->assertEquals([
            'path' => 'path/to/test.txt',
            'data' => [
                'alfa' => 'bravo',
                'charlie' => 'brown',
            ],
        ], $asset->getOriginal());

        // Test that the pending meta was resolved
        $this->assertSame($asset->getOriginal(), $asset->getRawOriginal());

        Storage::disk('test')->assertExists('path/to/.meta/test.txt.yaml');
        $this->assertEquals($yaml, Storage::disk('test')->get('path/to/.meta/test.txt.yaml'));
    }

    /** @test */
    public function it_resolves_pending_original_meta_values_when_hydrating()
    {
        Storage::disk('test')->put('path/to/test.txt', '');
        $yaml = <<<'YAML'
data:
  alfa: bravo
  charlie: delta
  echo: foxtrot
YAML;
        Storage::disk('test')->put('path/to/.meta/test.txt.yaml', $yaml);

        $asset = (new Asset)
            ->container($this->container)
            ->path('path/to/test.txt')
            ->set('charlie', 'brown');

        $this->assertEquals([], $asset->getRawOriginal());

        $asset->syncOriginal();

        $this->assertEquals([
            'path' => 'path/to/test.txt',
            'data' => new PendingMeta('data'),
        ], $asset->getRawOriginal());

        // Setting would trigger hydration, which would trigger the pending original values to be resolved.
        // We should not see this new value in the original state.
        $asset->set('golf', 'hotel');

        $this->assertEquals([
            'path' => 'path/to/test.txt',
            'data' => [
                'alfa' => 'bravo',
                'charlie' => 'brown',
                'echo' => 'foxtrot',
            ],
        ], $asset->getRawOriginal());
    }

    /**
     * @test
     *
     * @dataProvider warmPresetProvider
     */
    public function it_gets_which_presets_to_warm($extension, $orientation, $cpEnabled, $expectedWarm)
    {
        config(['statamic.cp.enabled' => $cpEnabled]);

        Storage::fake('test');

        if ($orientation === 'landscape') {
            $width = 20;
            $height = 10;
        } elseif ($orientation === 'portrait') {
            $width = 10;
            $height = 20;
        } elseif ($orientation === 'square') {
            $width = 10;
            $height = 10;
        }

        $filename = 'test.'.$extension;
        Storage::disk('test')->put($filename, '');

        if ($extension === 'jpg') {
            Storage::disk('test')->put('.meta/'.$filename.'.yaml', YAML::dump([
                'height' => $height,
                'width' => $width,
            ]));
        }

        $container = Facades\AssetContainer::make('test')->disk('test');
        $container = Mockery::mock($container)->makePartial();
        $container->shouldReceive('warmPresets')->andReturn(['one', 'two']);

        $asset = (new Asset)->container($container)->path($filename);

        $this->assertEquals($expectedWarm, $asset->warmPresets());
    }

    public static function warmPresetProvider()
    {
        return [
            'portrait' => ['jpg', 'portrait', true, ['one', 'two', 'cp_thumbnail_small_portrait']],
            'landscape' => ['jpg', 'landscape', true, ['one', 'two', 'cp_thumbnail_small_landscape']],
            'square' => ['jpg', 'square', true, ['one', 'two', 'cp_thumbnail_small_square']],
            'portrait svg' => ['svg', 'portrait', true, []],
            'landscape svg' => ['svg', 'landscape', true, []],
            'square svg' => ['svg', 'square', true, []],
            'non-image' => ['txt', null, true, []],
            'cp disabled, portrait' => ['jpg', 'portrait', false, ['one', 'two']],
            'cp disabled, landscape' => ['jpg', 'landscape', false, ['one', 'two']],
            'cp disabled, square' => ['jpg', 'square', false, ['one', 'two']],
            'cp disabled, portrait svg' => ['svg', 'portrait', false, []],
            'cp disabled, landscape svg' => ['svg', 'landscape', false, []],
            'cp disabled, square svg' => ['svg', 'square', false, []],
            'cp disabled, non-image' => ['txt', null, false, []],
        ];
    }

    private function fakeEventWithMacros()
    {
        $fake = Event::fake();
        $mock = \Mockery::mock($fake)->makePartial();
        $mock->shouldReceive('forgetListener');
        Event::swap($mock);
    }

    /** @test */
    public function it_fires_a_deleting_event()
    {
        Event::fake();

        $container = Facades\AssetContainer::make('test')->disk('test');
        Facades\AssetContainer::shouldReceive('findByHandle')->with('test')->andReturn($container);
        Facades\AssetContainer::shouldReceive('find')->with('test')->andReturn($container);

        Storage::disk('test')->put('foo/test.txt', '');
        $asset = (new Asset)->container('test')->path('foo/test.txt');

        $asset->delete();

        Event::assertDispatched(AssetDeleting::class, function ($event) use ($asset) {
            return $event->asset === $asset;
        });
    }

    /** @test */
    public function it_does_not_delete_when_a_deleting_event_returns_false()
    {
        Facades\Asset::spy();
        Event::fake([AssetDeleted::class]);

        Event::listen(AssetDeleting::class, function () {
            return false;
        });

        Storage::disk('test')->put('foo/test.txt', '');
        $asset = (new Asset)->container($this->container)->path('foo/test.txt');

        $return = $asset->delete();

        $this->assertFalse($return);
        Facades\Asset::shouldNotHaveReceived('delete');
        Event::assertNotDispatched(AssetDeleted::class);
    }
}

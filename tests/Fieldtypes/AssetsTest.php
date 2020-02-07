<?php

namespace Tests\Fieldtypes;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;
use Statamic\Contracts\Assets\Asset;
use Statamic\Facades\AssetContainer;
use Statamic\Fields\Field;
use Statamic\Fieldtypes\Assets\Assets;
use Statamic\Statamic;
use Tests\PreventSavingStacheItemsToDisk;
use Tests\TestCase;

class AssetsTest extends TestCase
{
    use PreventSavingStacheItemsToDisk;

    public function setUp(): void
    {
        parent::setUp();

        tap(Storage::fake('test'))->getDriver()->getConfig()->set('url', '/assets');
        Storage::disk('test')->put('foo/one.txt', '');
        Storage::disk('test')->put('bar/two.txt', '');
        AssetContainer::make('test')->disk('test')->save();
    }

    /** @test */
    function it_augments_to_a_collection_of_assets()
    {
        $augmented = $this->fieldtype()->augment(['foo/one.txt', 'bar/two.txt', 'unknown.txt']);

        $this->assertInstanceOf(Collection::class, $augmented);
        $this->assertCount(2, $augmented); // invalid assets get removed
        $this->assertEveryItemIsInstanceOf(Asset::class, $augmented);
        $this->assertEquals(['foo/one.txt', 'bar/two.txt'], $augmented->map->path()->all());
        $this->assertEquals(['one.txt', 'two.txt'], $augmented->map->basename()->all());
    }

    /** @test */
    function it_augments_to_a_single_asset_when_max_files_is_one()
    {
        $augmented = $this->fieldtype(['max_files' => 1])->augment(['foo/one.txt']);

        $this->assertInstanceOf(Asset::class, $augmented);
        $this->assertEquals('foo/one.txt', $augmented->path());
        $this->assertEquals('one.txt', $augmented->basename());
    }

    /** @test */
    function it_shallow_augments_to_a_collection_of_assets()
    {
        Statamic::enableShallowAugmentation();

        $augmented = $this->fieldtype()->augment(['foo/one.txt', 'bar/two.txt', 'unknown.txt']);

        $this->assertInstanceOf(Collection::class, $augmented);
        $this->assertEquals([
            [
                'id' => 'test::foo/one.txt',
                'url' => '/assets/foo/one.txt',
                'permalink' => 'http://localhost/assets/foo/one.txt'
            ],
            [
                'id' => 'test::bar/two.txt',
                'url' => '/assets/bar/two.txt',
                'permalink' => 'http://localhost/assets/bar/two.txt'
            ],
        ], $augmented->all());
    }

    /** @test */
    function it_shallow_augments_to_a_single_asset_when_max_files_is_one()
    {
        Statamic::enableShallowAugmentation();

        $augmented = $this->fieldtype(['max_files' => 1])->augment(['foo/one.txt']);

        $this->assertEquals([
            'id' => 'test::foo/one.txt',
            'url' => '/assets/foo/one.txt',
            'permalink' => 'http://localhost/assets/foo/one.txt'
        ], $augmented);
    }

    function fieldtype($config = [])
    {
        return (new Assets)->setField(new Field('test', array_merge([
            'type' => 'assets',
        ], $config)));
    }
}

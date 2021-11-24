<?php

namespace Tests\Fieldtypes;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;
use Statamic\Contracts\Assets\Asset;
use Statamic\Data\AugmentedCollection;
use Statamic\Facades\AssetContainer;
use Statamic\Fields\Field;
use Statamic\Fieldtypes\Assets\Assets;
use Statamic\Fieldtypes\Assets\DimensionsRule;
use Statamic\Fieldtypes\Assets\ImageRule;
use Statamic\Fieldtypes\Assets\MimesRule;
use Statamic\Fieldtypes\Assets\MimetypesRule;
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
    public function it_augments_to_a_collection_of_assets()
    {
        $augmented = $this->fieldtype()->augment(['foo/one.txt', 'bar/two.txt', 'unknown.txt']);

        $this->assertInstanceOf(Collection::class, $augmented);
        $this->assertCount(2, $augmented); // invalid assets get removed
        $this->assertEveryItemIsInstanceOf(Asset::class, $augmented);
        $this->assertEquals(['foo/one.txt', 'bar/two.txt'], $augmented->map->path()->all());
        $this->assertEquals(['one.txt', 'two.txt'], $augmented->map->basename()->all());
    }

    /** @test */
    public function it_augments_to_a_single_asset_when_max_files_is_one()
    {
        $augmented = $this->fieldtype(['max_files' => 1])->augment(['foo/one.txt']);

        $this->assertInstanceOf(Asset::class, $augmented);
        $this->assertEquals('foo/one.txt', $augmented->path());
        $this->assertEquals('one.txt', $augmented->basename());
    }

    /** @test */
    public function it_shallow_augments_to_a_collection_of_assets()
    {
        $augmented = $this->fieldtype()->shallowAugment(['foo/one.txt', 'bar/two.txt', 'unknown.txt']);

        $this->assertInstanceOf(Collection::class, $augmented);
        $this->assertEveryItemIsInstanceOf(AugmentedCollection::class, $augmented);
        $this->assertEquals([
            [
                'id' => 'test::foo/one.txt',
                'url' => '/assets/foo/one.txt',
                'permalink' => 'http://localhost/assets/foo/one.txt',
                'api_url' => 'http://localhost/api/assets/test/foo/one.txt',
            ],
            [
                'id' => 'test::bar/two.txt',
                'url' => '/assets/bar/two.txt',
                'permalink' => 'http://localhost/assets/bar/two.txt',
                'api_url' => 'http://localhost/api/assets/test/bar/two.txt',
            ],
        ], $augmented->toArray());
    }

    /** @test */
    public function it_shallow_augments_to_a_single_asset_when_max_files_is_one()
    {
        $augmented = $this->fieldtype(['max_files' => 1])->shallowAugment(['foo/one.txt']);

        $this->assertInstanceOf(AugmentedCollection::class, $augmented);
        $this->assertEquals([
            'id' => 'test::foo/one.txt',
            'url' => '/assets/foo/one.txt',
            'permalink' => 'http://localhost/assets/foo/one.txt',
            'api_url' => 'http://localhost/api/assets/test/foo/one.txt',
        ], $augmented->toArray());
    }

    /** @test */
    public function it_replaces_dimensions_rule()
    {
        $replaced = $this->fieldtype(['validate' => ['dimensions:width=180,height=180']])->fieldRules();

        $this->assertIsArray($replaced);
        $this->assertCount(1, $replaced);
        $this->assertInstanceOf(DimensionsRule::class, $replaced[0]);
        $this->assertEquals(__('statamic::validation.dimensions'), $replaced[0]->message());
    }

    /** @test */
    public function it_replaces_image_rule()
    {
        $replaced = $this->fieldtype(['validate' => ['image']])->fieldRules();

        $this->assertIsArray($replaced);
        $this->assertCount(1, $replaced);
        $this->assertInstanceOf(ImageRule::class, $replaced[0]);
        $this->assertEquals(__('statamic::validation.image'), $replaced[0]->message());
    }

    /** @test */
    public function it_replaces_mimes_rule()
    {
        $replaced = $this->fieldtype(['validate' => ['mimes:jpg,png']])->fieldRules();

        $this->assertIsArray($replaced);
        $this->assertCount(1, $replaced);
        $this->assertInstanceOf(MimesRule::class, $replaced[0]);
        $this->assertEquals(__('statamic::validation.mimes', ['values' => 'jpg, png, jpeg']), $replaced[0]->message());
    }

    /** @test */
    public function it_replaces_mimestypes_rule()
    {
        $replaced = $this->fieldtype(['validate' => ['mimetypes:image/jpg,image/png']])->fieldRules();

        $this->assertIsArray($replaced);
        $this->assertCount(1, $replaced);
        $this->assertInstanceOf(MimetypesRule::class, $replaced[0]);
        $this->assertEquals(__('statamic::validation.mimetypes', ['values' => 'image/jpg, image/png']), $replaced[0]->message());
    }

    /** @test */
    public function it_doesnt_replace_non_image_related_rule()
    {
        $replaced = $this->fieldtype(['validate' => ['min:3']])->fieldRules();

        $this->assertIsArray($replaced);
        $this->assertCount(1, $replaced);
        $this->assertEquals('min:3', $replaced[0]);
    }

    public function fieldtype($config = [])
    {
        return (new Assets)->setField(new Field('test', array_merge([
            'type' => 'assets',
        ], $config)));
    }
}

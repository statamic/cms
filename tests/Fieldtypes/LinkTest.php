<?php

namespace Tests\Fieldtypes;

use Facades\Statamic\Fields\BlueprintRepository;
use Illuminate\Support\Facades\Storage;
use Statamic\Assets\Asset;
use Statamic\Entries\Entry;
use Statamic\Facades;
use Statamic\Facades\AssetContainer;
use Statamic\Fields\Field;
use Statamic\Fieldtypes\Link;
use Tests\PreventSavingStacheItemsToDisk;
use Tests\TestCase;

class LinkTest extends TestCase
{
    use PreventSavingStacheItemsToDisk;

    public function setup(): void
    {
        parent::setUp();

        $collection = tap(Facades\Collection::make('pages'))->routes('/{slug}')->save();

        $blueprint = Facades\Blueprint::make('article')
            ->setNamespace('collections.pages')
            ->setContents([
                'fields' => [
                    [
                        'handle' => 'link',
                        'field' => [
                            'type' => 'link',
                            'collections' => ['pages'],
                        ],
                    ],
                ],
            ]);

        BlueprintRepository::shouldReceive('in')->with('collections/pages')->andReturn(collect([$blueprint]));

        Storage::fake('test', ['url' => '/assets']);
        Storage::disk('test')->put('foo/one.txt', '');
        Storage::disk('test')->put('bar/two.txt', '');

        AssetContainer::make('test')->disk('test')->save();
    }

    /** @test */
    public function it_augments_string_to_string()
    {
        $parent = tap(new Entry)->collection('pages')->slug('the-redirect')->id('test')->save();
        $field = new Field('test', ['type' => 'link']);
        $field->setParent($parent);
        $fieldtype = (new Link)->setField($field);

        $this->assertEquals('/foo', $fieldtype->augment('/foo'));
    }

    /** @test */
    public function it_augments_entry_to_url()
    {
        $parent = tap(new Entry)->collection('pages')->slug('the-redirect')->id('test')->save();
        $field = new Field('test', ['type' => 'link']);
        $field->setParent($parent);
        $fieldtype = (new Link)->setField($field);

        $this->assertEquals('/the-redirect', $fieldtype->augment('entry::test'));
    }

    /** @test */
    public function it_augments_entry_to_entry()
    {
        $parent = tap(new Entry)->collection('pages')->slug('the-redirect')->id('test')->save();
        $field = new Field('test', ['type' => 'link']);
        $field->setParent($parent);
        $fieldtype = (new Link)->setField($field);

        $this->assertTrue($fieldtype->augment('entry::test')->value() instanceof Entry);
    }

    /** @test */
    public function it_augments_invalid_entry_to_null()
    {
        $parent = tap(new Entry)->collection('pages')->slug('the-redirect')->id('test')->save();
        $field = new Field('test', ['type' => 'link']);
        $field->setParent($parent);
        $fieldtype = (new Link)->setField($field);

        $this->assertNull($fieldtype->augment('entry::foo'));
    }

    /** @test */
    public function it_augments_asset_to_url()
    {
        $parent = tap(new Entry)->collection('pages')->slug('the-redirect')->id('test')->save();
        $field = new Field('test', ['type' => 'link']);
        $field->setParent($parent);
        $fieldtype = (new Link)->setField($field);

        $this->assertEquals('/assets/foo/one.txt', $fieldtype->augment('asset::test::foo/one.txt'));
    }

    /** @test */
    public function it_augments_asset_to_asset()
    {
        $parent = tap(new Entry)->collection('pages')->slug('the-redirect')->id('test')->save();
        $field = new Field('test', ['type' => 'link']);
        $field->setParent($parent);
        $fieldtype = (new Link)->setField($field);

        $this->assertTrue($fieldtype->augment('asset::test::foo/one.txt')->value() instanceof Asset);
    }

    /** @test */
    public function it_augments_invalid_asset_to_null()
    {
        $parent = tap(new Entry)->collection('pages')->slug('the-redirect')->id('test')->save();
        $field = new Field('test', ['type' => 'link']);
        $field->setParent($parent);
        $fieldtype = (new Link)->setField($field);

        $this->assertNull($fieldtype->augment('asset::test::foo/three.txt'));
    }

    /** @test */
    public function it_augments_null_to_null()
    {
        $parent = tap(new Entry)->collection('pages')->slug('the-redirect')->id('test')->save();
        $field = new Field('test', ['type' => 'link']);
        $field->setParent(new Entry);
        $fieldtype = (new Link)->setField($field);

        $this->assertNull($fieldtype->augment(null));
    }
}

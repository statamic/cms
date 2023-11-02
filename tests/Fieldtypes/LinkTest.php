<?php

namespace Tests\Fieldtypes;

use Facades\Statamic\Fields\BlueprintRepository;
use Facades\Statamic\Routing\ResolveRedirect;
use Statamic\Entries\Entry;
use Statamic\Facades;
use Statamic\Fields\Field;
use Statamic\Fieldtypes\Link;
use Tests\TestCase;

class LinkTest extends TestCase
{
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
    public function it_augments_invalid_entry_to_null()
    {
        $parent = tap(new Entry)->collection('pages')->slug('the-redirect')->id('test')->save();
        $field = new Field('test', ['type' => 'link']);
        $field->setParent($parent);
        $fieldtype = (new Link)->setField($field);

        $this->assertNull($fieldtype->augment('entry::foo'));
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

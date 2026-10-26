<?php

namespace Tests\Fields;

use Facades\Statamic\Fields\FieldRepository;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Fields\Field;
use Statamic\Fields\Fields;
use Statamic\Fields\Section;
use Tests\TestCase;

class SectionTest extends TestCase
{
    #[Test]
    public function it_gets_the_display()
    {
        $this->assertNull((new Section([]))->display());

        $this->assertEquals('Test', (new Section(['display' => 'Test']))->display());
    }

    #[Test]
    public function it_gets_the_instructions()
    {
        $this->assertNull((new Section([]))->instructions());

        $this->assertEquals('Test', (new Section(['instructions' => 'Test']))->instructions());
    }

    #[Test]
    public function it_gets_contents()
    {
        $section = new Section($contents = ['foo' => 'bar']);

        $this->assertEquals($contents, $section->contents());
    }

    #[Test]
    public function it_gets_fields()
    {
        $section = new Section([]);
        tap($section->fields(), function ($fields) {
            $this->assertInstanceOf(Fields::class, $fields);
            $this->assertCount(0, $fields->all());
        });

        FieldRepository::shouldReceive('find')
            ->with('fieldset_one.field_one')
            ->andReturn(new Field('field_one', ['type' => 'text']));
        FieldRepository::shouldReceive('find')
            ->with('fieldset_one.field_two')
            ->andReturn(new Field('field_one', ['type' => 'textarea']));

        $section = new Section([
            'fields' => [
                [
                    'handle' => 'one',
                    'field' => 'fieldset_one.field_one',
                ],
                [
                    'handle' => 'two',
                    'field' => 'fieldset_one.field_two',
                ],
            ],
        ]);

        tap($section->fields(), function ($fields) {
            $this->assertInstanceOf(Fields::class, $fields);
            tap($fields->all(), function ($items) {
                $this->assertCount(2, $items->all());
                $this->assertEveryItemIsInstanceOf(Field::class, $items);
                $this->assertEquals(['one', 'two'], $items->map->handle()->values()->all());
                $this->assertEquals(['text', 'textarea'], $items->map->type()->values()->all());
            });
        });
    }

    #[Test]
    public function converts_to_array_suitable_for_rendering_fields_in_publish_component()
    {
        FieldRepository::shouldReceive('find')
            ->with('fieldset_one.field_one')
            ->andReturn(new Field('field_one', [
                'type' => 'text',
                'display' => 'One',
                'instructions' => 'One instructions',
                'validate' => 'required|min:2',
            ]));
        FieldRepository::shouldReceive('find')
            ->with('fieldset_one.field_two')
            ->andReturn(new Field('field_two', [
                'type' => 'textarea',
                'display' => 'Two',
                'instructions' => 'Two instructions',
                'validate' => 'min:2',
            ]));

        $section = new Section([
            'display' => 'Test Section',
            'instructions' => 'Does stuff',
            'fields' => [
                [
                    'handle' => 'one',
                    'field' => 'fieldset_one.field_one',
                ],
                [
                    'handle' => 'two',
                    'field' => 'fieldset_one.field_two',
                ],
            ],
        ]);

        $this->assertSame([
            'display' => 'Test Section',
            'instructions' => 'Does stuff',
            'fields' => [
                [
                    'display' => 'One',
                    'hide_display' => false,
                    'handle' => 'one',
                    'instructions' => 'One instructions',
                    'instructions_position' => 'above',
                    'listable' => 'hidden',
                    'visibility' => 'visible',
                    'sortable' => true,
                    'replicator_preview' => true,
                    'duplicate' => true,
                    'actions' => true,
                    'type' => 'text',
                    'input_type' => 'text',
                    'character_limit' => null,
                    'autocomplete' => null,
                    'placeholder' => null,
                    'prepend' => null,
                    'append' => null,
                    'default' => null,
                    'antlers' => false,
                    'component' => 'text',
                    'prefix' => null,
                    'required' => true,
                    'read_only' => false, // deprecated
                    'always_save' => false,
                    'reserve_space_when_hidden' => false,
                ],
                [
                    'display' => 'Two',
                    'hide_display' => false,
                    'handle' => 'two',
                    'instructions' => 'Two instructions',
                    'instructions_position' => 'above',
                    'listable' => 'hidden',
                    'visibility' => 'visible',
                    'sortable' => true,
                    'replicator_preview' => true,
                    'duplicate' => true,
                    'actions' => true,
                    'type' => 'textarea',
                    'placeholder' => null,
                    'character_limit' => null,
                    'rows' => null,
                    'default' => null,
                    'antlers' => false,
                    'component' => 'textarea',
                    'prefix' => null,
                    'required' => false,
                    'read_only' => false, // deprecated
                    'always_save' => false,
                    'reserve_space_when_hidden' => false,
                ],
            ],
        ], $section->toPublishArray());
    }

    #[Test]
    public function it_can_be_given_resolved_fields()
    {
        $fields = (new Fields)->setFields(collect([
            'seo_title' => (new Field('seo_title', ['type' => 'text']))->setPrefix('seo_'),
        ]));

        $section = new Section(['display' => 'SEO', 'fields' => [['handle' => 'ignored', 'field' => ['type' => 'text']]]], $fields);

        $this->assertSame($fields, $section->fields());

        $publish = $section->toPublishArray();
        $this->assertEquals('SEO', $publish['display']);
        $this->assertEquals(['seo_title'], collect($publish['fields'])->pluck('handle')->all());
        $this->assertEquals('seo_', $publish['fields'][0]['prefix']);
    }

    #[Test]
    public function resolved_fields_dont_share_cached_publish_arrays_with_fields_that_differ_by_prefix()
    {
        $inline = new Section(['fields' => [['handle' => 'home_city', 'field' => ['type' => 'text']]]]);

        $imported = new Section([], (new Fields)->setFields(collect([
            'home_city' => (new Field('home_city', ['type' => 'text']))->setPrefix('home_'),
        ])));

        $this->assertNull($inline->toPublishArray()['fields'][0]['prefix']);
        $this->assertEquals('home_', $imported->toPublishArray()['fields'][0]['prefix']);
    }
}

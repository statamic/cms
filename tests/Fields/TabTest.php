<?php

namespace Tests\Fields;

use Facades\Statamic\Fields\FieldRepository;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Facades\Fieldset as FieldsetRepository;
use Statamic\Fields\Field;
use Statamic\Fields\Fields;
use Statamic\Fields\Fieldset;
use Statamic\Fields\Tab;
use Tests\TestCase;

class TabTest extends TestCase
{
    #[Test]
    public function it_gets_the_handle()
    {
        $tab = new Tab('test');

        $this->assertEquals('test', $tab->handle());
    }

    #[Test]
    public function it_gets_contents()
    {
        $tab = new Tab('test');
        $this->assertEquals([], $tab->contents());

        $contents = [
            'fields' => ['one' => ['type' => 'text']],
        ];

        $return = $tab->setContents($contents);

        $this->assertEquals($tab, $return);
        $this->assertEquals($contents, $tab->contents());
    }

    #[Test]
    public function it_gets_the_display_text()
    {
        $tab = (new Tab('test'))->setContents([
            'display' => 'The Display Text',
        ]);

        $this->assertEquals('The Display Text', $tab->display());
    }

    #[Test]
    public function the_display_text_falls_back_to_a_humanized_handle()
    {
        $tab = new Tab('the_tab_handle');

        $this->assertEquals('The tab handle', $tab->display());
    }

    #[Test]
    public function it_gets_fields()
    {
        $tab = new Tab('test');
        tap($tab->fields(), function ($fields) {
            $this->assertInstanceOf(Fields::class, $fields);
            $this->assertCount(0, $fields->all());
        });

        FieldRepository::shouldReceive('find')
            ->with('fieldset_one.field_one')
            ->andReturn(new Field('field_one', ['type' => 'text']));
        FieldRepository::shouldReceive('find')
            ->with('fieldset_one.field_two')
            ->andReturn(new Field('field_one', ['type' => 'textarea']));

        $tab->setContents($contents = [
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

        tap($tab->fields(), function ($fields) {
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

        $tab = (new Tab('test'))->setContents([
            'display' => 'Test Tab',
            'instructions' => 'Does stuff',
            'sections' => [
                [
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
                ],
            ],
        ]);

        $this->assertSame([
            'display' => 'Test Tab',
            'instructions' => 'Does stuff',
            'handle' => 'test',
            'sections' => [
                [
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
                            'default' => null,
                            'antlers' => false,
                            'component' => 'textarea',
                            'prefix' => null,
                            'required' => false,
                            'read_only' => false, // deprecated
                            'always_save' => false,
                        ],
                    ],
                ],
            ],
        ], $tab->toPublishArray());
    }

    #[Test]
    public function it_expands_sectioned_fieldset_imports_into_publish_sections()
    {
        FieldsetRepository::shouldReceive('find')
            ->with('seo')
            ->andReturn((new Fieldset)->setHandle('seo')->setContents([
                'sections' => [
                    [
                        'display' => 'SEO',
                        'fields' => [
                            ['handle' => 'meta_title', 'field' => ['type' => 'text']],
                        ],
                    ],
                    [
                        'display' => 'Social',
                        'fields' => [
                            ['handle' => 'og_title', 'field' => ['type' => 'text']],
                        ],
                    ],
                ],
            ]));

        $tab = (new Tab('main'))->setContents([
            'sections' => [
                [
                    'display' => 'Main',
                    'fields' => [
                        ['handle' => 'title', 'field' => ['type' => 'text']],
                        ['import' => 'seo'],
                        ['handle' => 'summary', 'field' => ['type' => 'textarea']],
                    ],
                ],
            ],
        ]);

        $publish = $tab->toPublishArray();

        $this->assertCount(4, $publish['sections']);
        $this->assertEquals('Main', $publish['sections'][0]['display']);
        $this->assertEquals(['title'], collect($publish['sections'][0]['fields'])->pluck('handle')->all());
        $this->assertEquals('SEO', $publish['sections'][1]['display']);
        $this->assertEquals(['meta_title'], collect($publish['sections'][1]['fields'])->pluck('handle')->all());
        $this->assertEquals('Social', $publish['sections'][2]['display']);
        $this->assertEquals(['og_title'], collect($publish['sections'][2]['fields'])->pluck('handle')->all());
        $this->assertEquals('Main', $publish['sections'][3]['display']);
        $this->assertEquals(['summary'], collect($publish['sections'][3]['fields'])->pluck('handle')->all());
    }

    #[Test]
    public function it_applies_prefixes_to_fields_inside_imported_fieldset_sections()
    {
        FieldsetRepository::shouldReceive('find')
            ->with('seo')
            ->andReturn((new Fieldset)->setHandle('seo')->setContents([
                'sections' => [
                    [
                        'display' => 'SEO',
                        'fields' => [
                            ['handle' => 'meta_title', 'field' => ['type' => 'text']],
                        ],
                    ],
                ],
            ]));

        $tab = (new Tab('main'))->setContents([
            'sections' => [
                [
                    'fields' => [
                        ['import' => 'seo', 'prefix' => 'seo_'],
                    ],
                ],
            ],
        ]);

        $publish = $tab->toPublishArray();
        $field = $publish['sections'][0]['fields'][0];

        $this->assertEquals('seo_meta_title', $field['handle']);
        $this->assertEquals('seo_', $field['prefix']);
    }

    #[Test]
    public function it_can_flatten_imported_fieldset_sections_in_place()
    {
        FieldsetRepository::shouldReceive('find')
            ->with('seo')
            ->andReturn((new Fieldset)->setHandle('seo')->setContents([
                'sections' => [
                    [
                        'display' => 'SEO',
                        'fields' => [
                            ['handle' => 'meta_title', 'field' => ['type' => 'text']],
                        ],
                    ],
                ],
            ]));

        $tab = (new Tab('main'))->setContents([
            'sections' => [
                [
                    'display' => 'Main',
                    'fields' => [
                        ['handle' => 'title', 'field' => ['type' => 'text']],
                        ['import' => 'seo', 'section_behavior' => 'flatten'],
                        ['handle' => 'summary', 'field' => ['type' => 'textarea']],
                    ],
                ],
            ],
        ]);

        $publish = $tab->toPublishArray();

        $this->assertCount(1, $publish['sections']);
        $this->assertEquals('Main', $publish['sections'][0]['display']);
        $this->assertEquals(['title', 'meta_title', 'summary'], collect($publish['sections'][0]['fields'])->pluck('handle')->all());
    }

    #[Test]
    public function it_applies_config_overrides_to_fields_inside_imported_fieldset_sections()
    {
        FieldsetRepository::shouldReceive('find')
            ->with('seo')
            ->andReturn((new Fieldset)->setHandle('seo')->setContents([
                'sections' => [
                    [
                        'display' => 'SEO',
                        'fields' => [
                            ['handle' => 'meta_title', 'field' => ['type' => 'text', 'display' => 'Meta Title']],
                            ['handle' => 'meta_description', 'field' => ['type' => 'textarea', 'display' => 'Meta Description']],
                        ],
                    ],
                ],
            ]));

        $tab = (new Tab('main'))->setContents([
            'sections' => [
                [
                    'fields' => [
                        [
                            'import' => 'seo',
                            'config' => [
                                'meta_title' => ['instructions' => 'Keep it under 60 characters.'],
                            ],
                        ],
                    ],
                ],
            ],
        ]);

        $publish = $tab->toPublishArray();
        $fields = collect($publish['sections'][0]['fields']);

        $metaTitle = $fields->firstWhere('handle', 'meta_title');
        $this->assertEquals('Keep it under 60 characters.', $metaTitle['instructions']);
        $this->assertEquals('Meta Title', $metaTitle['display']);

        $metaDescription = $fields->firstWhere('handle', 'meta_description');
        $this->assertNull($metaDescription['instructions']);
        $this->assertEquals('Meta Description', $metaDescription['display']);
    }
}

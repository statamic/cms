<?php

namespace Tests\Forms\Fields;

use Facades\Statamic\Console\Processes\Composer;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Facades;
use Statamic\Fields\Fieldset;
use Statamic\Forms\Fields\FormField;
use Statamic\Forms\Fields\FormFields;
use Tests\TestCase;

class FormFieldsTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Composer::shouldReceive('isInstalled')->with('statamic/forms-pro')->andReturn(false)->byDefault();
    }

    #[Test]
    public function it_returns_raw_contents()
    {
        $contents = [
            'sections' => [
                [
                    'display' => 'Contact Info',
                    'fields' => [
                        ['handle' => 'name', 'field' => ['type' => 'short_answer', 'display' => 'Name']],
                        ['handle' => 'email', 'field' => ['type' => 'email']],
                        ['import' => 'test'],
                    ],
                ],
            ],
        ];

        $formFields = new FormFields($contents);

        $this->assertSame($contents, $formFields->contents());
    }

    #[Test]
    public function it_flattens_pages_to_sections_when_forms_pro_is_not_installed()
    {
        $formFields = new FormFields([
            'pages' => [
                [
                    'sections' => [
                        ['display' => 'Section A', 'fields' => [['handle' => 'name', 'field' => ['type' => 'short_answer']]]],
                    ],
                ],
                [
                    'sections' => [
                        ['display' => 'Section B', 'fields' => [['handle' => 'email', 'field' => ['type' => 'email']]]],
                    ],
                ],
            ],
        ]);

        $this->assertArrayNotHasKey('pages', $formFields->contents());
        $this->assertArrayHasKey('sections', $formFields->contents());
        $this->assertCount(2, $formFields->contents()['sections']);
        $this->assertEquals('Section A', $formFields->contents()['sections'][0]['display']);
        $this->assertEquals('Section B', $formFields->contents()['sections'][1]['display']);
    }

    #[Test]
    public function it_keeps_pages_when_forms_pro_is_installed()
    {
        Composer::shouldReceive('isInstalled')->with('statamic/forms-pro')->andReturn(true);

        $formFields = new FormFields([
            'pages' => [
                ['sections' => [['fields' => []]]],
            ],
        ]);

        $this->assertArrayHasKey('pages', $formFields->contents());
        $this->assertArrayNotHasKey('sections', $formFields->contents());
    }

    #[Test]
    public function it_returns_pages()
    {
        Composer::shouldReceive('isInstalled')->with('statamic/forms-pro')->andReturn(true);

        $formFields = new FormFields([
            'pages' => [
                ['sections' => [['fields' => []]]],
                ['sections' => [['fields' => []]]],
            ],
        ]);

        $pages = $formFields->pages();

        $this->assertCount(2, $pages);
        $this->assertEquals([['fields' => []]], $pages->get(0)['sections']);
        $this->assertEquals([['fields' => []]], $pages->get(1)['sections']);
    }

    #[Test]
    public function it_collapses_pages_to_single_page_when_forms_pro_is_not_installed()
    {
        $formFields = new FormFields([
            'pages' => [
                ['sections' => [['display' => 'Section A', 'fields' => []]]],
                ['sections' => [['display' => 'Section B', 'fields' => []]]],
            ],
        ]);

        $pages = $formFields->pages();

        $this->assertCount(1, $pages);
        $this->assertCount(2, $pages->first()['sections']);
        $this->assertEquals('Section A', $pages->first()['sections'][0]['display']);
        $this->assertEquals('Section B', $pages->first()['sections'][1]['display']);
    }

    #[Test]
    public function it_returns_single_page_when_using_sections()
    {
        $formFields = new FormFields([
            'sections' => [['fields' => []]],
        ]);

        $pages = $formFields->pages();

        $this->assertCount(1, $pages);
        $this->assertEquals([['fields' => []]], $pages->first()['sections']);
    }

    #[Test]
    public function it_returns_sections()
    {
        $formFields = new FormFields([
            'sections' => [
                ['display' => 'Section One', 'fields' => []],
                ['display' => 'Section Two', 'instructions' => 'Some instructions', 'fields' => []],
            ],
        ]);

        $sections = $formFields->sections();

        $this->assertCount(2, $sections);
        $this->assertEquals('Section One', $sections->first()['display']);
        $this->assertEquals('Section Two', $sections->last()['display']);
        $this->assertEquals('Some instructions', $sections->last()['instructions']);
    }

    #[Test]
    public function it_returns_items()
    {
        $formFields = new FormFields([
            'sections' => [
                [
                    'display' => 'Section One',
                    'fields' => [
                        ['handle' => 'email', 'field' => ['type' => 'email']],
                    ],
                ],
                [
                    'display' => 'Section Two',
                    'fields' => [
                        ['handle' => 'name', 'field' => ['type' => 'short_answer']],
                        ['handle' => 'message', 'field' => ['type' => 'long_answer']],
                    ],
                ],
            ],
        ]);

        $this->assertEquals([
            ['handle' => 'email', 'field' => ['type' => 'email']],
            ['handle' => 'name', 'field' => ['type' => 'short_answer']],
            ['handle' => 'message', 'field' => ['type' => 'long_answer']],
        ], $formFields->items()->all());
    }

    #[Test]
    public function it_returns_items_from_pages()
    {
        Composer::shouldReceive('isInstalled')->with('statamic/forms-pro')->andReturn(true);

        $formFields = new FormFields([
            'pages' => [
                [
                    'sections' => [
                        [
                            'display' => 'Section One',
                            'fields' => [
                                ['handle' => 'name', 'field' => ['type' => 'short_answer']],
                            ],
                        ],
                    ],
                ],
                [
                    'sections' => [
                        [
                            'display' => 'Section Two',
                            'fields' => [
                                ['handle' => 'email', 'field' => ['type' => 'email']],
                                ['handle' => 'message', 'field' => ['type' => 'long_answer']],
                            ],
                        ],
                    ],
                ],
            ],
        ]);

        $this->assertEquals([
            ['handle' => 'name', 'field' => ['type' => 'short_answer']],
            ['handle' => 'email', 'field' => ['type' => 'email']],
            ['handle' => 'message', 'field' => ['type' => 'long_answer']],
        ], $formFields->items()->all());
    }

    #[Test]
    public function it_returns_items_from_fieldsets()
    {
        $fieldset = (new Fieldset)->setContents([
            'fields' => [
                ['handle' => 'imported_field', 'field' => ['type' => 'text']],
            ],
        ]);

        Facades\Fieldset::shouldReceive('find')
            ->with('test')
            ->andReturn($fieldset);

        $formFields = new FormFields([
            'sections' => [
                [
                    'fields' => [
                        ['import' => 'test'],
                        ['import' => 'test', 'prefix' => 'prefixed_'],
                        ['handle' => 'renamed_imported_field', 'field' => 'test.imported_field', 'config' => ['display' => 'Renamed Imported Field']],
                    ],
                ],
            ],
        ]);

        $this->assertEquals([
            ['import' => 'test'],
            ['import' => 'test', 'prefix' => 'prefixed_'],
            ['handle' => 'renamed_imported_field', 'field' => 'test.imported_field', 'config' => ['display' => 'Renamed Imported Field']],
        ], $formFields->items()->all());
    }

    #[Test]
    public function it_returns_fields()
    {
        $formFields = new FormFields([
            'sections' => [
                [
                    'display' => 'Section One',
                    'fields' => [
                        ['handle' => 'email', 'field' => ['type' => 'email']],
                    ],
                ],
                [
                    'display' => 'Section Two',
                    'fields' => [
                        ['handle' => 'name', 'field' => ['type' => 'short_answer']],
                        ['handle' => 'message', 'field' => ['type' => 'long_answer']],
                    ],
                ],
            ],
        ]);

        $fields = $formFields->fields();

        $this->assertEveryItemIsInstanceOf(FormField::class, $fields->all());
        $this->assertEquals('email', $fields->get('email')->type());
        $this->assertEquals('short_answer', $fields->get('name')->type());
        $this->assertEquals('long_answer', $fields->get('message')->type());
    }

    #[Test]
    public function it_returns_fields_from_pages()
    {
        Composer::shouldReceive('isInstalled')->with('statamic/forms-pro')->andReturn(true);

        $formFields = new FormFields([
            'pages' => [
                [
                    'sections' => [
                        ['fields' => [['handle' => 'name', 'field' => ['type' => 'short_answer']]]],
                    ],
                ],
                [
                    'sections' => [
                        ['fields' => [['handle' => 'email', 'field' => ['type' => 'email']]]],
                    ],
                ],
            ],
        ]);

        $fields = $formFields->fields();

        $this->assertEveryItemIsInstanceOf(FormField::class, $fields->all());
        $this->assertEquals(['name', 'email'], $fields->keys()->all());
    }

    #[Test]
    public function it_returns_fields_from_fieldsets()
    {
        $fieldset = (new Fieldset)->setContents([
            'fields' => [
                ['handle' => 'imported_field', 'field' => ['type' => 'text']],
            ],
        ]);

        Facades\Fieldset::shouldReceive('find')
            ->with('test')
            ->andReturn($fieldset);

        $formFields = new FormFields([
            'sections' => [
                [
                    'fields' => [
                        ['import' => 'test'],
                        ['import' => 'test', 'prefix' => 'prefixed_'],
                        ['handle' => 'renamed_imported_field', 'field' => 'test.imported_field', 'config' => ['display' => 'Renamed Imported Field']],
                    ],
                ],
            ],
        ]);

        $fields = $formFields->fields();

        $this->assertEveryItemIsInstanceOf(FormField::class, $fields->all());
        $this->assertEquals(['imported_field', 'prefixed_imported_field', 'renamed_imported_field'], $fields->keys()->all());
        $this->assertEveryItem($fields->map->type()->values()->all(), fn (string $type) => $type === 'text');
    }

    #[Test]
    public function it_returns_a_single_field()
    {
        $formFields = new FormFields([
            'sections' => [
                [
                    'fields' => [
                        ['handle' => 'name', 'field' => ['type' => 'short_answer']],
                        ['handle' => 'email', 'field' => ['type' => 'email']],
                    ],
                ],
            ],
        ]);

        $field = $formFields->field('name');

        $this->assertInstanceOf(FormField::class, $field);
        $this->assertEquals('name', $field->handle());
        $this->assertEquals('short_answer', $field->type());
    }

    #[Test]
    public function it_returns_null_for_nonexistent_field()
    {
        $formFields = new FormFields([
            'sections' => [
                ['fields' => [['handle' => 'email', 'field' => ['type' => 'email']]]],
            ],
        ]);

        $this->assertNull($formFields->field('nonexistent'));
    }

    #[Test]
    public function it_converts_to_blueprint()
    {
        $formFields = new FormFields([
            'sections' => [
                [
                    'display' => 'Contact Info',
                    'fields' => [
                        ['handle' => 'name', 'field' => ['type' => 'short_answer', 'display' => 'Name']],
                        ['handle' => 'email', 'field' => ['type' => 'email', 'display' => 'Email Address']],
                    ],
                ],
                [
                    'display' => 'Additional Info',
                    'fields' => [
                        ['handle' => 'shopping_list', 'field' => ['type' => 'list', 'display' => 'Shopping List']],
                    ],
                ],
            ],
        ]);

        $blueprint = $formFields->toBlueprint();

        $this->assertEquals([
            'tabs' => [
                'main' => [
                    'sections' => [
                        [
                            'display' => 'Contact Info',
                            'fields' => [
                                [
                                    'handle' => 'name',
                                    'field' => [
                                        'type' => 'text',
                                        'display' => 'Name',
                                    ],
                                ],
                                [
                                    'handle' => 'email',
                                    'field' => [
                                        'type' => 'text',
                                        'validate' => ['email'],
                                        'input_type' => 'email',
                                        'display' => 'Email Address',
                                    ],
                                ],
                            ],
                        ],
                        [
                            'display' => 'Additional Info',
                            'fields' => [
                                ['handle' => 'shopping_list', 'field' => ['type' => 'list', 'display' => 'Shopping List']],
                            ],
                        ],
                    ],
                    'display' => 'Page 1 of 1',
                ],
            ],
        ], $blueprint->contents());
    }

    #[Test]
    public function it_converts_pages_to_blueprint_with_tabs()
    {
        Composer::shouldReceive('isInstalled')->with('statamic/forms-pro')->andReturn(true);

        $formFields = new FormFields([
            'pages' => [
                [
                    'sections' => [
                        [
                            'display' => 'Contact Info',
                            'fields' => [
                                ['handle' => 'name', 'field' => ['type' => 'short_answer', 'display' => 'Name']],
                            ],
                        ],
                    ],
                ],
                [
                    'sections' => [
                        [
                            'display' => 'Additional Details',
                            'fields' => [
                                ['handle' => 'message', 'field' => ['type' => 'long_answer', 'display' => 'Message']],
                            ],
                        ],
                    ],
                ],
            ],
        ]);

        $blueprint = $formFields->toBlueprint();

        $this->assertEquals([
            'tabs' => [
                'page_1' => [
                    'sections' => [
                        [
                            'display' => 'Contact Info',
                            'fields' => [
                                ['handle' => 'name', 'field' => ['type' => 'text', 'display' => 'Name']],
                            ],
                        ],
                    ],
                    'display' => 'Page 1 of 2',
                ],
                'page_2' => [
                    'sections' => [
                        [
                            'display' => 'Additional Details',
                            'fields' => [
                                ['handle' => 'message', 'field' => ['type' => 'textarea', 'display' => 'Message']],
                            ],
                        ],
                    ],
                    'display' => 'Page 2 of 2',
                ],
            ],
        ], $blueprint->contents());
    }

    #[Test]
    public function it_converts_to_a_blueprint_with_fieldsets()
    {
        $fieldset = (new Fieldset)->setContents([
            'fields' => [
                ['handle' => 'imported_field', 'field' => ['type' => 'text']],
            ],
        ]);

        Facades\Fieldset::shouldReceive('find')
            ->with('test')
            ->andReturn($fieldset);

        $formFields = new FormFields([
            'sections' => [
                [
                    'fields' => [
                        ['import' => 'test'],
                        ['import' => 'test', 'prefix' => 'prefixed_'],
                        ['handle' => 'renamed_imported_field', 'field' => 'test.imported_field', 'config' => ['display' => 'Renamed Imported Field']],
                    ],
                ],
            ],
        ]);

        $blueprint = $formFields->toBlueprint();

        $this->assertEquals([
            'tabs' => [
                'main' => [
                    'sections' => [
                        [
                            'fields' => [
                                ['import' => 'test'],
                                ['import' => 'test', 'prefix' => 'prefixed_'],
                                ['handle' => 'renamed_imported_field', 'field' => 'test.imported_field', 'config' => ['display' => 'Renamed Imported Field']],
                            ],
                        ],
                    ],
                    'display' => 'Page 1 of 1',
                ],
            ],
        ], $blueprint->contents());
    }

    #[Test]
    public function it_excludes_hidden_fields_from_blueprint()
    {
        $formFields = new FormFields([
            'sections' => [
                [
                    'display' => 'Contact Info',
                    'fields' => [
                        ['handle' => 'name', 'field' => ['type' => 'short_answer', 'display' => 'Name']],
                        ['handle' => 'email', 'field' => ['type' => 'email', 'display' => 'Email', 'hidden' => true]],
                        ['handle' => 'message', 'field' => ['type' => 'long_answer', 'display' => 'Message']],
                    ],
                ],
            ],
        ]);

        $blueprint = $formFields->toBlueprint();

        $fields = $blueprint->contents()['tabs']['main']['sections'][0]['fields'];

        $this->assertCount(2, $fields);
        $this->assertEquals('name', $fields[0]['handle']);
        $this->assertEquals('message', $fields[1]['handle']);
    }
}

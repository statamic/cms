<?php

namespace Tests\Forms\Fields;

use PHPUnit\Framework\Attributes\Test;
use Statamic\Facades;
use Statamic\Fields\Fieldset;
use Statamic\Forms\Fields\FormField;
use Statamic\Forms\Fields\FormFields;
use Tests\TestCase;

class FormFieldsTest extends TestCase
{
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
                        // Should be converted to their "normal fieldtype" equivalent.
                        ['handle' => 'name', 'field' => ['type' => 'short_answer', 'display' => 'Name']],
                        ['handle' => 'email', 'field' => ['type' => 'email', 'display' => 'Email Address']],
                    ],
                ],
                [
                    'display' => 'Additional Info',
                    'fields' => [
                        // List isn't a form fieldtype, so its config shouldn't be touched.
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
                ],
            ],
        ], $blueprint->contents());
    }
}

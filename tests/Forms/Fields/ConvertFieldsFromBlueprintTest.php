<?php

namespace Tests\Forms\Fields;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Facades;
use Statamic\Facades\Blueprint;
use Statamic\Facades\Form;
use Statamic\Fields\Fieldset;
use Tests\TestCase;

class ConvertFieldsFromBlueprintTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();

        Form::all()->each->delete();
    }

    public function tearDown(): void
    {
        if ($blueprint = Blueprint::find('forms.contact_us')) {
            Blueprint::delete($blueprint);
        }

        parent::tearDown();
    }

    #[Test]
    #[DataProvider('fieldConversionProvider')]
    public function it_converts_fields(array $originalBlueprintField, array $expectedFormField)
    {
        $this->makeBlueprint([
            'tabs' => [
                'main' => [
                    'sections' => [
                        [
                            'fields' => [
                                [
                                    'handle' => 'field',
                                    'field' => $originalBlueprintField,
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ]);

        $form = Form::make('contact_us');

        $this->assertEquals([
            'handle' => 'field',
            'field' => $expectedFormField,
        ], $form->formFields()->items()->first());
    }

    public static function fieldConversionProvider(): array
    {
        return [
            'email' => [
                ['type' => 'text', 'display' => 'Email', 'input_type' => 'email', 'validate' => ['email']],
                ['type' => 'email', 'display' => 'Email'],
            ],
            'email, with validation rules' => [
                ['type' => 'text', 'display' => 'Email', 'input_type' => 'email', 'validate' => ['required', 'email']],
                ['type' => 'email', 'display' => 'Email', 'validate' => ['required']],
            ],
            'email, with parameterized email rule' => [
                ['type' => 'text', 'display' => 'Email', 'input_type' => 'email', 'validate' => ['required', 'email:rfc,strict']],
                ['type' => 'email', 'display' => 'Email', 'validate' => ['required']],
            ],
            'email, pipe-string parameterized email rule' => [
                ['type' => 'text', 'display' => 'Email', 'input_type' => 'email', 'validate' => 'required|email:rfc'],
                ['type' => 'email', 'display' => 'Email', 'validate' => ['required']],
            ],
            'website' => [
                ['type' => 'text', 'display' => 'Website', 'input_type' => 'url', 'validate' => ['url']],
                ['type' => 'website', 'display' => 'Website'],
            ],
            'website, with validation rules' => [
                ['type' => 'text', 'display' => 'Website', 'input_type' => 'url', 'validate' => ['required', 'url']],
                ['type' => 'website', 'display' => 'Website', 'validate' => ['required']],
            ],
            'phone' => [
                ['type' => 'text', 'display' => 'Phone', 'input_type' => 'tel'],
                ['type' => 'phone', 'display' => 'Phone'],
            ],
            'phone, with placeholder' => [
                ['type' => 'text', 'display' => 'Phone', 'input_type' => 'tel', 'placeholder' => '(555) 123-4567'],
                ['type' => 'phone', 'display' => 'Phone', 'placeholder' => '(555) 123-4567'],
            ],
            'short_answer' => [
                ['type' => 'text', 'display' => 'Name', 'input_type' => 'text'],
                ['type' => 'short_answer', 'display' => 'Name'],
            ],
            'text with input_type' => [
                ['type' => 'text', 'input_type' => 'date'],
                ['type' => 'text', 'input_type' => 'date'],
            ],
            'long_answer' => [
                ['type' => 'textarea', 'display' => 'Message'],
                ['type' => 'long_answer', 'display' => 'Message'],
            ],
            'number' => [
                ['type' => 'integer', 'display' => 'Age'],
                ['type' => 'number', 'display' => 'Age'],
            ],
            'time_picker' => [
                ['type' => 'time', 'display' => 'Start Time'],
                ['type' => 'time_picker', 'display' => 'Start Time'],
            ],
            'dropdown' => [
                ['type' => 'select', 'display' => 'Color', 'options' => ['red' => 'Red', 'blue' => 'Blue']],
                ['type' => 'dropdown', 'display' => 'Color', 'options' => ['red' => 'Red', 'blue' => 'Blue']],
            ],
            'dropdown, removes multiple and max_items when single' => [
                ['type' => 'select', 'display' => 'Color', 'multiple' => false, 'max_items' => 1, 'options' => ['red' => 'Red']],
                ['type' => 'dropdown', 'display' => 'Color', 'options' => ['red' => 'Red']],
            ],
            'dropdown, with multiple' => [
                ['type' => 'select', 'display' => 'Colors', 'multiple' => true, 'options' => ['red' => 'Red']],
                ['type' => 'dropdown', 'display' => 'Colors', 'multiple' => true, 'options' => ['red' => 'Red']],
            ],
            'dropdown, with multiple converts max_items to max_selections' => [
                ['type' => 'select', 'display' => 'Colors', 'multiple' => true, 'max_items' => 3, 'options' => ['red' => 'Red']],
                ['type' => 'dropdown', 'display' => 'Colors', 'multiple' => true, 'max_selections' => 3, 'options' => ['red' => 'Red']],
            ],
            'multi_choice' => [
                ['type' => 'radio', 'display' => 'Color', 'options' => ['red' => 'Red', 'blue' => 'Blue']],
                ['type' => 'multi_choice', 'display' => 'Color', 'options' => ['red' => 'Red', 'blue' => 'Blue']],
            ],
            'non-form field should be preserved' => [
                ['type' => 'video', 'display' => 'Video', 'default' => 'https://www.youtube.com/watch?v=dQw4w9WgXcQ'],
                ['type' => 'video', 'display' => 'Video', 'default' => 'https://www.youtube.com/watch?v=dQw4w9WgXcQ'],
            ],
            'upload from assets' => [
                ['type' => 'assets', 'display' => 'Documents', 'container' => 'documents', 'folder' => 'uploads', 'max_files' => 3],
                ['type' => 'upload', 'display' => 'Documents', 'store' => true, 'container' => 'documents', 'folder' => 'uploads', 'max_files' => 3],
            ],
            'upload from assets, minimal config' => [
                ['type' => 'assets', 'display' => 'Photo', 'container' => 'images'],
                ['type' => 'upload', 'display' => 'Photo', 'store' => true, 'container' => 'images'],
            ],
            'upload from files' => [
                ['type' => 'files', 'display' => 'Attachments', 'max_files' => 5],
                ['type' => 'upload', 'display' => 'Attachments', 'store' => false, 'max_files' => 5],
            ],
            'upload from files, minimal config' => [
                ['type' => 'files', 'display' => 'Resume'],
                ['type' => 'upload', 'display' => 'Resume', 'store' => false],
            ],
        ];
    }

    #[Test]
    public function it_preserves_fieldsets()
    {
        $fieldset = (new Fieldset)->setContents([
            'fields' => [
                ['handle' => 'imported_field', 'field' => ['type' => 'text']],
            ],
        ]);

        Facades\Fieldset::shouldReceive('find')
            ->with('test')
            ->andReturn($fieldset);

        $this->makeBlueprint([
            'tabs' => [
                'main' => [
                    'sections' => [
                        [
                            'fields' => [
                                ['handle' => 'normal_field', 'field' => ['type' => 'text']],
                                ['import' => 'test'],
                                ['import' => 'test', 'prefix' => 'prefixed_'],
                                ['handle' => 'renamed_imported_field', 'field' => 'test.imported_field', 'config' => ['display' => 'Renamed Imported Field']],
                            ],
                        ],
                    ],
                ],
            ],
        ]);

        $form = Form::make('contact_us');

        $this->assertEquals([
            'sections' => [
                [
                    'fields' => [
                        ['handle' => 'normal_field', 'field' => ['type' => 'short_answer']],
                        ['import' => 'test'],
                        ['import' => 'test', 'prefix' => 'prefixed_'],
                        ['handle' => 'renamed_imported_field', 'field' => 'test.imported_field', 'config' => ['display' => 'Renamed Imported Field']],
                    ],
                ],
            ],
        ], $form->formFields()->contents());
    }

    #[Test]
    public function it_flattens_tabs_into_sections()
    {
        $this->makeBlueprint([
            'tabs' => [
                'one' => [
                    'sections' => [
                        [
                            'display' => 'Section One',
                            'fields' => [
                                ['handle' => 'foo', 'field' => ['type' => 'text']],
                            ],
                        ],
                        [
                            'display' => 'Section Two',
                            'fields' => [
                                ['handle' => 'bar', 'field' => ['type' => 'text']],
                            ],
                        ],
                    ],
                ],
                'two' => [
                    'sections' => [
                        [
                            'display' => 'Section Three',
                            'fields' => [
                                ['handle' => 'baz', 'field' => ['type' => 'text']],
                            ],
                        ],
                    ],
                ],
            ],
        ]);

        $form = Form::make('contact_us');

        $contents = $form->formFields()->contents();

        $this->assertArrayHasKey('sections', $contents);
        $this->assertArrayNotHasKey('tabs', $contents);
        $this->assertCount(3, $contents['sections']);
    }

    #[Test]
    public function it_handles_legacy_format_with_no_tabs()
    {
        $this->makeBlueprint([
            'sections' => [
                'main' => [
                    'display' => 'Section One',
                    'fields' => [
                        ['handle' => 'foo', 'field' => ['type' => 'text']],
                    ],
                ],
            ],
        ]);

        $form = Form::make('contact_us');

        $contents = $form->formFields()->contents();

        $this->assertArrayHasKey('sections', $contents);
        $this->assertArrayNotHasKey('tabs', $contents);
        $this->assertCount(1, $contents['sections']);
    }

    private function makeBlueprint(array $contents): void
    {
        Blueprint::make()->setHandle('contact_us')->setNamespace('forms')->setContents($contents)->save();
    }
}

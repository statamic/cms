<?php

namespace Tests\Forms\Fields;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Facades\Blueprint;
use Statamic\Facades\Form;
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
            'short_answer' => [
                ['type' => 'text', 'display' => 'Name'],
                ['type' => 'short_answer', 'display' => 'Name'],
            ],
            'long_answer' => [
                ['type' => 'textarea', 'display' => 'Message'],
                ['type' => 'long_answer', 'display' => 'Message'],
            ],
            'non-form field should be preserved' => [
                ['type' => 'video', 'display' => 'Video', 'default' => 'https://www.youtube.com/watch?v=dQw4w9WgXcQ'],
                ['type' => 'video', 'display' => 'Video', 'default' => 'https://www.youtube.com/watch?v=dQw4w9WgXcQ'],
            ],
        ];
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

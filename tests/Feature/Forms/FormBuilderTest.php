<?php

namespace Tests\Feature\Forms;

use Facades\Statamic\Console\Processes\Composer;
use Facades\Statamic\Fields\FieldtypeRepository;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Facades\Form;
use Statamic\Facades\User;
use Statamic\Fieldtypes\Link;
use Statamic\Fieldtypes\Text;
use Statamic\Forms\Fields\FormFieldtype;
use Tests\FakesRoles;
use Tests\PreventSavingStacheItemsToDisk;
use Tests\TestCase;

class FormBuilderTest extends TestCase
{
    use FakesRoles;
    use PreventSavingStacheItemsToDisk;

    protected function setUp(): void
    {
        parent::setUp();

        Composer::shouldReceive('isInstalled')->with('statamic/forms-pro')->andReturn(false)->byDefault();
    }

    #[Test]
    public function it_shows_the_form_builder_if_you_have_permission()
    {
        $this->setTestRoles(['test' => ['access cp', 'configure forms']]);
        $user = User::make()->assignRole('test')->save();
        $form = tap(Form::make('test'))->save();

        $this
            ->actingAs($user)
            ->get(cp_route('forms.builder.edit', $form->handle()))
            ->assertSuccessful()
            ->assertInertia(fn ($page) => $page
                ->component('forms/Builder')
                ->has('form')
                ->has('initialFormFields')
                ->has('fieldtypes')
                ->has('action')
            );
    }

    #[Test]
    public function it_denies_access_if_you_dont_have_permission()
    {
        $this->setTestRoles(['test' => ['access cp']]);
        $user = tap(User::make()->assignRole('test'))->save();
        $form = tap(Form::make('test'))->save();

        $this
            ->from('/original')
            ->actingAs($user)
            ->get(cp_route('forms.builder.edit', $form->handle()))
            ->assertRedirect('/original')
            ->assertSessionHas('error');
    }

    #[Test]
    public function it_provides_initial_form_fields()
    {
        $this->setTestRoles(['test' => ['access cp', 'configure forms']]);
        $user = User::make()->assignRole('test')->save();

        $form = tap(Form::make('test')->formFields([
            'pages' => [
                [
                    'sections' => [
                        [
                            'display' => 'Main Section',
                            'fields' => [
                                ['handle' => 'name', 'field' => ['type' => 'short_answer', 'display' => 'Name']],
                                ['handle' => 'email', 'field' => ['type' => 'short_answer', 'display' => 'Email']],
                            ],
                        ],
                    ],
                ],
            ],
        ]))->save();

        $this
            ->actingAs($user)
            ->get(cp_route('forms.builder.edit', $form->handle()))
            ->assertSuccessful()
            ->assertInertia(fn ($page) => $page
                ->component('forms/Builder')
                ->has('initialFormFields.pages', 1)
                ->has('initialFormFields.pages.0.sections', 1)
                ->has('initialFormFields.pages.0.sections.0.fields', 2)
            );
    }

    #[Test]
    public function it_provides_available_fieldtypes()
    {
        $this->setTestRoles(['test' => ['access cp', 'configure forms']]);
        $user = User::make()->assignRole('test')->save();
        $form = tap(Form::make('test'))->save();

        $response = $this
            ->actingAs($user)
            ->get(cp_route('forms.builder.edit', $form->handle()))
            ->assertSuccessful();

        $fieldtypes = $response->viewData('page')['props']['fieldtypes'];
        $this->assertTrue(collect($fieldtypes)->contains('handle', 'short_answer'));
    }

    #[Test]
    public function it_includes_regular_fieldtypes_made_selectable_via_deprecated_method()
    {
        Link::makeSelectableInForms();

        $this->setTestRoles(['test' => ['access cp', 'configure forms']]);
        $user = User::make()->assignRole('test')->save();
        $form = tap(Form::make('test'))->save();

        $response = $this
            ->actingAs($user)
            ->get(cp_route('forms.builder.edit', $form->handle()))
            ->assertSuccessful();

        $fieldtypes = collect($response->viewData('page')['props']['fieldtypes']);
        $linkFieldtype = $fieldtypes->firstWhere('handle', 'link');

        $this->assertNotNull($linkFieldtype);
        $this->assertEquals([], $linkFieldtype['categories']);
    }

    #[Test]
    public function it_excludes_form_fieldtypes_when_wrapped_fieldtype_is_made_unselectable()
    {
        Text::makeUnselectableInForms();

        $this->setTestRoles(['test' => ['access cp', 'configure forms']]);
        $user = User::make()->assignRole('test')->save();
        $form = tap(Form::make('test'))->save();

        $response = $this
            ->actingAs($user)
            ->get(cp_route('forms.builder.edit', $form->handle()))
            ->assertSuccessful();

        $fieldtypes = collect($response->viewData('page')['props']['fieldtypes']);

        $this->assertNull($fieldtypes->firstWhere('handle', 'short_answer'));
    }

    #[Test]
    public function it_can_update_form_fields()
    {
        $this->setTestRoles(['test' => ['access cp', 'configure forms']]);
        $user = User::make()->assignRole('test')->save();
        $form = tap(Form::make('test'))->save();

        $payload = [
            'pages' => [
                [
                    '_id' => 'page1',
                    'display' => null,
                    'instructions' => null,
                    'button_label' => null,
                    'previous_page_label' => null,
                    'sections' => [
                        [
                            '_id' => 'section1',
                            'display' => 'Contact Info',
                            'fields' => [
                                [
                                    '_id' => 'field1',
                                    'handle' => 'name',
                                    'type' => 'inline',
                                    'fieldtype' => 'short_answer',
                                    'config' => [
                                        'type' => 'short_answer',
                                        'display' => 'Your Name',
                                        'placeholder' => 'Enter your name',
                                    ],
                                ],
                                [
                                    '_id' => 'field2',
                                    'handle' => 'email',
                                    'type' => 'inline',
                                    'fieldtype' => 'short_answer',
                                    'config' => [
                                        'type' => 'short_answer',
                                        'display' => 'Email Address',
                                        'placeholder' => 'Enter your email',
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ];

        $this
            ->actingAs($user)
            ->patch(cp_route('forms.builder.update', $form->handle()), $payload)
            ->assertSuccessful();

        $form = Form::find('test');
        $formFields = $form->formFields();

        $this->assertCount(1, $formFields->pages());
        $this->assertCount(1, $formFields->pages()[0]['sections']);
        $this->assertCount(2, $formFields->pages()[0]['sections'][0]['fields']);
        $this->assertEquals('name', $formFields->pages()[0]['sections'][0]['fields'][0]['handle']);
        $this->assertEquals('email', $formFields->pages()[0]['sections'][0]['fields'][1]['handle']);
    }

    #[Test]
    public function it_denies_update_without_permission()
    {
        $this->setTestRoles(['test' => ['access cp']]);
        $user = tap(User::make()->assignRole('test'))->save();
        $form = tap(Form::make('test'))->save();

        $payload = [
            'pages' => [
                [
                    '_id' => 'page1',
                    'sections' => [
                        [
                            '_id' => 'section1',
                            'display' => 'Section',
                            'fields' => [],
                        ],
                    ],
                ],
            ],
        ];

        $this
            ->from('/original')
            ->actingAs($user)
            ->patch(cp_route('forms.builder.update', $form->handle()), $payload)
            ->assertRedirect('/original')
            ->assertSessionHas('error');
    }

    #[Test]
    public function it_validates_field_configs_and_returns_errors_keyed_by_field_id()
    {
        $this->registerFieldtypeWithRequiredConfig();

        $this->setTestRoles(['test' => ['access cp', 'configure forms']]);
        $user = User::make()->assignRole('test')->save();
        $form = tap(Form::make('test'))->save();

        $payload = [
            'pages' => [
                [
                    '_id' => 'page1',
                    'sections' => [
                        [
                            '_id' => 'section1',
                            'display' => 'Section',
                            'fields' => [
                                [
                                    '_id' => 'abc123',
                                    'handle' => 'my_field',
                                    'type' => 'inline',
                                    'fieldtype' => 'test_required_config',
                                    'config' => [
                                        'type' => 'test_required_config',
                                        'display' => 'My Field',
                                        // 'required_option' is required but missing
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ];

        $response = $this
            ->actingAs($user)
            ->patch(cp_route('forms.builder.update', $form->handle()), $payload);

        $response->assertSessionHasErrors();
        $errors = $response->exception->errors();

        // Errors should be keyed by field _id with dot notation
        $this->assertArrayHasKey('abc123.required_option', $errors);
    }

    #[Test]
    public function it_validates_multiple_fields_and_returns_all_errors()
    {
        $this->registerFieldtypeWithRequiredConfig();

        $this->setTestRoles(['test' => ['access cp', 'configure forms']]);
        $user = User::make()->assignRole('test')->save();
        $form = tap(Form::make('test'))->save();

        $payload = [
            'pages' => [
                [
                    '_id' => 'page1',
                    'sections' => [
                        [
                            '_id' => 'section1',
                            'display' => 'Section',
                            'fields' => [
                                [
                                    '_id' => 'field1_id',
                                    'handle' => 'field1',
                                    'type' => 'inline',
                                    'fieldtype' => 'test_required_config',
                                    'config' => [
                                        'type' => 'test_required_config',
                                        // missing required_option
                                    ],
                                ],
                                [
                                    '_id' => 'field2_id',
                                    'handle' => 'field2',
                                    'type' => 'inline',
                                    'fieldtype' => 'test_required_config',
                                    'config' => [
                                        'type' => 'test_required_config',
                                        // missing required_option
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ];

        $response = $this
            ->actingAs($user)
            ->patch(cp_route('forms.builder.update', $form->handle()), $payload);

        $response->assertSessionHasErrors();
        $errors = $response->exception->errors();

        // Both field errors should be present
        $this->assertArrayHasKey('field1_id.required_option', $errors);
        $this->assertArrayHasKey('field2_id.required_option', $errors);
    }

    #[Test]
    public function it_can_update_with_multiple_pages()
    {
        Composer::shouldReceive('isInstalled')->with('statamic/forms-pro')->andReturn(true);

        $this->setTestRoles(['test' => ['access cp', 'configure forms']]);
        $user = User::make()->assignRole('test')->save();
        $form = tap(Form::make('test'))->save();

        $payload = [
            'pages' => [
                [
                    '_id' => 'page1',
                    'display' => 'Personal Info',
                    'instructions' => 'Please provide your personal information.',
                    'button_label' => 'Next',
                    'previous_page_label' => null,
                    'sections' => [
                        [
                            '_id' => 'section1',
                            'display' => 'Section',
                            'fields' => [
                                [
                                    '_id' => 'field1',
                                    'handle' => 'name',
                                    'type' => 'inline',
                                    'fieldtype' => 'short_answer',
                                    'config' => [
                                        'type' => 'short_answer',
                                        'display' => 'Name',
                                        'placeholder' => 'Your name',
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
                [
                    '_id' => 'page2',
                    'display' => 'Contact Info',
                    'instructions' => 'How can we reach you?',
                    'button_label' => 'Submit',
                    'previous_page_label' => 'Go Back',
                    'sections' => [
                        [
                            '_id' => 'section2',
                            'display' => 'Section',
                            'fields' => [
                                [
                                    '_id' => 'field2',
                                    'handle' => 'email',
                                    'type' => 'inline',
                                    'fieldtype' => 'short_answer',
                                    'config' => [
                                        'type' => 'short_answer',
                                        'display' => 'Email',
                                        'placeholder' => 'Your email',
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ];

        $this
            ->actingAs($user)
            ->patch(cp_route('forms.builder.update', $form->handle()), $payload)
            ->assertSuccessful();

        $form = Form::find('test');
        $formFields = $form->formFields();

        $this->assertCount(2, $formFields->pages());
        $this->assertEquals('Personal Info', $formFields->pages()[0]['display']);
        $this->assertEquals('Contact Info', $formFields->pages()[1]['display']);
    }

    #[Test]
    public function it_can_update_with_multiple_sections()
    {
        $this->setTestRoles(['test' => ['access cp', 'configure forms']]);
        $user = User::make()->assignRole('test')->save();
        $form = tap(Form::make('test'))->save();

        $payload = [
            'pages' => [
                [
                    '_id' => 'page1',
                    'sections' => [
                        [
                            '_id' => 'section1',
                            'display' => 'Personal',
                            'fields' => [
                                [
                                    '_id' => 'field1',
                                    'handle' => 'name',
                                    'type' => 'inline',
                                    'fieldtype' => 'short_answer',
                                    'config' => [
                                        'type' => 'short_answer',
                                        'display' => 'Name',
                                        'placeholder' => 'Your name',
                                    ],
                                ],
                            ],
                        ],
                        [
                            '_id' => 'section2',
                            'display' => 'Contact',
                            'fields' => [
                                [
                                    '_id' => 'field2',
                                    'handle' => 'email',
                                    'type' => 'inline',
                                    'fieldtype' => 'short_answer',
                                    'config' => [
                                        'type' => 'short_answer',
                                        'display' => 'Email',
                                        'placeholder' => 'Your email',
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ];

        $this
            ->actingAs($user)
            ->patch(cp_route('forms.builder.update', $form->handle()), $payload)
            ->assertSuccessful();

        $form = Form::find('test');
        $formFields = $form->formFields();

        $this->assertCount(2, $formFields->pages()[0]['sections']);
        $this->assertEquals('Personal', $formFields->pages()[0]['sections'][0]['display']);
        $this->assertEquals('Contact', $formFields->pages()[0]['sections'][1]['display']);
    }

    #[Test]
    public function it_skips_validation_for_import_fields()
    {
        $this->setTestRoles(['test' => ['access cp', 'configure forms']]);
        $user = User::make()->assignRole('test')->save();
        $form = tap(Form::make('test'))->save();

        $payload = [
            'pages' => [
                [
                    '_id' => 'page1',
                    'sections' => [
                        [
                            '_id' => 'section1',
                            'display' => 'Section',
                            'fields' => [
                                [
                                    '_id' => 'import1',
                                    'type' => 'import',
                                    'fieldset' => 'some_fieldset',
                                    'prefix' => null,
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ];

        $this
            ->actingAs($user)
            ->patch(cp_route('forms.builder.update', $form->handle()), $payload)
            ->assertSuccessful();
    }

    #[Test]
    public function it_filters_out_link_fields_placeholders_when_saving()
    {
        $this->setTestRoles(['test' => ['access cp', 'configure forms']]);
        $user = User::make()->assignRole('test')->save();
        $form = tap(Form::make('test'))->save();

        $payload = [
            'pages' => [
                [
                    '_id' => 'page1',
                    'sections' => [
                        [
                            '_id' => 'section1',
                            'display' => 'Section',
                            'fields' => [
                                [
                                    '_id' => 'placeholder1',
                                    'type' => 'link_fields',
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ];

        $this
            ->actingAs($user)
            ->patch(cp_route('forms.builder.update', $form->handle()), $payload)
            ->assertSuccessful();

        $form = Form::find('test');
        $this->assertEmpty($form->formFields()->pages()[0]['sections'][0]['fields'] ?? []);
    }

    private function registerFieldtypeWithRequiredConfig(): void
    {
        $formFieldtype = new class extends FormFieldtype
        {
            public static $handle = 'test_required_config';

            public function configFieldItems(): array
            {
                return [
                    'required_option' => ['type' => 'text', 'validate' => 'required'],
                ];
            }

            public function toFieldArray(): array
            {
                return ['type' => 'text'];
            }
        };

        $formFieldtype::register();
    }
}

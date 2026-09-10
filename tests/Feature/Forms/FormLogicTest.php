<?php

namespace Tests\Feature\Forms;

use Facades\Statamic\Console\Processes\Composer;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Facades\Form;
use Statamic\Facades\User;
use Tests\FakesRoles;
use Tests\PreventSavingStacheItemsToDisk;
use Tests\TestCase;

class FormLogicTest extends TestCase
{
    use FakesRoles;
    use PreventSavingStacheItemsToDisk;

    protected function setUp(): void
    {
        parent::setUp();

        Composer::shouldReceive('isInstalled')->with('statamic/forms-pro')->andReturn(false)->byDefault();
    }

    #[Test]
    public function it_shows_the_logic_page_if_you_have_permission()
    {
        $this->setTestRoles(['test' => ['access cp', 'configure forms']]);
        $user = tap(User::make()->assignRole('test'))->save();
        $form = tap(Form::make('test'))->save();

        $this
            ->actingAs($user)
            ->get(cp_route('forms.logic.edit', $form->handle()))
            ->assertSuccessful()
            ->assertInertia(fn ($page) => $page
                ->component('forms/Logic')
                ->has('form')
                ->has('formFields.pages')
                ->has('action')
            );
    }

    #[Test]
    public function it_shows_the_logic_page_with_the_edit_forms_permission()
    {
        $this->setTestRoles(['test' => ['access cp', 'edit forms']]);
        $user = tap(User::make()->assignRole('test'))->save();
        $form = tap(Form::make('test'))->save();

        $this
            ->actingAs($user)
            ->get(cp_route('forms.logic.edit', $form->handle()))
            ->assertSuccessful()
            ->assertInertia(fn ($page) => $page->component('forms/Logic'));
    }

    #[Test]
    public function it_shows_the_logic_page_with_the_edit_form_permission()
    {
        $this->setTestRoles(['test' => ['access cp', 'edit test form']]);
        $user = tap(User::make()->assignRole('test'))->save();
        $form = tap(Form::make('test'))->save();

        $this
            ->actingAs($user)
            ->get(cp_route('forms.logic.edit', $form->handle()))
            ->assertSuccessful()
            ->assertInertia(fn ($page) => $page->component('forms/Logic'));
    }

    #[Test]
    public function it_shows_the_logic_page_with_the_configure_form_fields_permission()
    {
        $this->setTestRoles(['test' => ['access cp', 'configure form fields']]);
        $user = tap(User::make()->assignRole('test'))->save();
        $form = tap(Form::make('test'))->save();

        $this
            ->actingAs($user)
            ->get(cp_route('forms.logic.edit', $form->handle()))
            ->assertSuccessful()
            ->assertInertia(fn ($page) => $page->component('forms/Logic'));
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
            ->get(cp_route('forms.logic.edit', $form->handle()))
            ->assertRedirect('/original')
            ->assertSessionHas('error');
    }

    #[Test]
    public function it_denies_access_with_only_submission_permissions()
    {
        $this->setTestRoles(['test' => ['access cp', 'view form submissions', 'view test form submissions']]);
        $user = tap(User::make()->assignRole('test'))->save();
        $form = tap(Form::make('test'))->save();

        $this
            ->from('/original')
            ->actingAs($user)
            ->get(cp_route('forms.logic.edit', $form->handle()))
            ->assertRedirect('/original')
            ->assertSessionHas('error');
    }

    #[Test]
    public function it_provides_the_nested_form_fields_structure()
    {
        Composer::shouldReceive('isInstalled')->with('statamic/forms-pro')->andReturn(true);

        $this->setTestRoles(['test' => ['access cp', 'configure forms']]);
        $user = tap(User::make()->assignRole('test'))->save();

        $form = tap(Form::make('test')->formFields([
            'pages' => [
                [
                    'id' => 'page1',
                    'display' => 'First Page',
                    'rules' => [
                        [
                            'conditions' => [['field' => 'name', 'operator' => 'equals', 'value' => 'test']],
                            'destination' => 'page2',
                        ],
                    ],
                    'sections' => [
                        [
                            'display' => 'Contact',
                            'fields' => [
                                ['handle' => 'name', 'field' => ['type' => 'short_answer', 'display' => 'Name', 'if' => ['email' => 'not empty']]],
                            ],
                        ],
                    ],
                ],
                [
                    'id' => 'page2',
                    'display' => 'Second Page',
                    'sections' => [['display' => 'Section', 'fields' => []]],
                ],
            ],
        ]))->save();

        $this
            ->actingAs($user)
            ->get(cp_route('forms.logic.edit', $form->handle()))
            ->assertSuccessful()
            ->assertInertia(fn ($page) => $page
                ->component('forms/Logic')
                ->has('formFields.pages', 2)
                ->where('formFields.pages.0._id', 'page1')
                ->where('formFields.pages.0.display', 'First Page')
                ->has('formFields.pages.0.rules', 1)
                ->where('formFields.pages.0.rules.0.destination', 'page2')
                ->where('formFields.pages.0.sections.0.display', 'Contact')
                ->where('formFields.pages.0.sections.0.fields.0.handle', 'name')
                ->where('formFields.pages.0.sections.0.fields.0.config.if', ['email' => 'not empty'])
            );
    }

    #[Test]
    public function it_can_update_field_conditions()
    {
        $this->setTestRoles(['test' => ['access cp', 'configure forms']]);
        $user = tap(User::make()->assignRole('test'))->save();

        $form = tap(Form::make('test')->formFields([
            'pages' => [
                [
                    'id' => 'page1',
                    'sections' => [
                        [
                            'display' => 'Section',
                            'fields' => [
                                ['handle' => 'name', 'field' => ['type' => 'short_answer', 'display' => 'Name']],
                                ['handle' => 'email', 'field' => ['type' => 'short_answer', 'display' => 'Email']],
                            ],
                        ],
                    ],
                ],
            ],
        ]))->save();

        $payload = [
            'pages' => [
                [
                    '_id' => 'page1',
                    'sections' => [
                        [
                            'display' => 'Section',
                            'fields' => [
                                ['type' => 'inline', 'handle' => 'name', 'fieldtype' => 'short_answer', 'config' => ['type' => 'short_answer', 'display' => 'Name']],
                                ['type' => 'inline', 'handle' => 'email', 'fieldtype' => 'short_answer', 'config' => ['type' => 'short_answer', 'display' => 'Email', 'if' => ['name' => 'not empty'], 'always_save' => true]],
                            ],
                        ],
                    ],
                ],
            ],
        ];

        $this
            ->actingAs($user)
            ->patch(cp_route('forms.logic.update', $form->handle()), $payload)
            ->assertSuccessful();

        $fields = Form::find('test')->formFields()->pages()[0]['sections'][0]['fields'];

        $this->assertArrayNotHasKey('if', $fields[0]['field']);
        $this->assertEquals(['name' => 'not empty'], $fields[1]['field']['if']);
        $this->assertTrue($fields[1]['field']['always_save']);
    }

    #[Test]
    public function it_persists_field_reordering()
    {
        $this->setTestRoles(['test' => ['access cp', 'configure forms']]);
        $user = tap(User::make()->assignRole('test'))->save();

        $form = tap(Form::make('test')->formFields([
            'pages' => [
                [
                    'id' => 'page1',
                    'sections' => [
                        [
                            'display' => 'Section',
                            'fields' => [
                                ['handle' => 'name', 'field' => ['type' => 'short_answer', 'display' => 'Name']],
                                ['handle' => 'email', 'field' => ['type' => 'short_answer', 'display' => 'Email']],
                                ['handle' => 'phone', 'field' => ['type' => 'short_answer', 'display' => 'Phone']],
                            ],
                        ],
                    ],
                ],
            ],
        ]))->save();

        $field = fn (string $handle) => ['type' => 'inline', 'handle' => $handle, 'fieldtype' => 'short_answer', 'config' => ['type' => 'short_answer', 'display' => ucfirst($handle)]];

        $payload = [
            'pages' => [
                [
                    '_id' => 'page1',
                    'sections' => [
                        ['display' => 'Section', 'fields' => [$field('phone'), $field('name'), $field('email')]],
                    ],
                ],
            ],
        ];

        $this
            ->actingAs($user)
            ->patch(cp_route('forms.logic.update', $form->handle()), $payload)
            ->assertSuccessful();

        $fields = Form::find('test')->formFields()->pages()[0]['sections'][0]['fields'];

        $this->assertSame(['phone', 'name', 'email'], array_column($fields, 'handle'));
    }

    #[Test]
    public function it_can_update_conditions_on_a_referenced_field()
    {
        // A referenced field stores its `field` as a string handle, so its logic
        // conditions are saved as overrides under `config` rather than inside `field`.
        $this->setTestRoles(['test' => ['access cp', 'configure forms']]);
        $user = tap(User::make()->assignRole('test'))->save();

        $form = tap(Form::make('test')->formFields([
            'pages' => [
                [
                    'id' => 'page1',
                    'sections' => [
                        [
                            'display' => 'Section',
                            'fields' => [
                                ['handle' => 'name', 'field' => ['type' => 'short_answer', 'display' => 'Name']],
                                ['handle' => 'message', 'field' => 'testing.message'],
                            ],
                        ],
                    ],
                ],
            ],
        ]))->save();

        $payload = [
            'pages' => [
                [
                    '_id' => 'page1',
                    'sections' => [
                        [
                            'display' => 'Section',
                            'fields' => [
                                ['type' => 'inline', 'handle' => 'name', 'fieldtype' => 'short_answer', 'config' => ['type' => 'short_answer', 'display' => 'Name']],
                                ['type' => 'reference', 'handle' => 'message', 'field_reference' => 'testing.message', 'config' => ['if' => ['name' => 'not empty']], 'config_overrides' => ['if']],
                            ],
                        ],
                    ],
                ],
            ],
        ];

        $this
            ->actingAs($user)
            ->patch(cp_route('forms.logic.update', $form->handle()), $payload)
            ->assertSuccessful();

        $fields = Form::find('test')->formFields()->pages()[0]['sections'][0]['fields'];

        $this->assertSame('testing.message', $fields[1]['field']);
        $this->assertEquals(['name' => 'not empty'], $fields[1]['config']['if']);
    }

    #[Test]
    public function it_can_update_page_rules()
    {
        Composer::shouldReceive('isInstalled')->with('statamic/forms-pro')->andReturn(true);

        $this->setTestRoles(['test' => ['access cp', 'configure forms']]);
        $user = tap(User::make()->assignRole('test'))->save();

        $form = tap(Form::make('test')->formFields([
            'pages' => [
                ['id' => 'page1', 'sections' => [['display' => 'Section', 'fields' => []]]],
                ['id' => 'page2', 'sections' => [['display' => 'Section', 'fields' => []]]],
            ],
        ]))->save();

        $payload = [
            'pages' => [
                [
                    '_id' => 'page1',
                    'rules' => [
                        [
                            '_id' => 'rule1',
                            'conditions' => [['_id' => 'cond1', 'field' => 'favorite_color', 'operator' => 'equals', 'value' => 'blue']],
                            'destination' => 'page2',
                        ],
                    ],
                    'sections' => [['display' => 'Section', 'fields' => []]],
                ],
                ['_id' => 'page2', 'sections' => [['display' => 'Section', 'fields' => []]]],
            ],
        ];

        $this
            ->actingAs($user)
            ->patch(cp_route('forms.logic.update', $form->handle()), $payload)
            ->assertSuccessful();

        $rules = Form::find('test')->formFields()->pages()[0]['rules'];

        $this->assertCount(1, $rules);
        $this->assertEquals('page2', $rules[0]['destination']);
        $this->assertEquals('favorite_color', $rules[0]['conditions'][0]['field']);
        $this->assertArrayNotHasKey('_id', $rules[0]);
        $this->assertArrayNotHasKey('_id', $rules[0]['conditions'][0]);
    }

    #[Test]
    public function it_saves_page_rule_conditions_with_a_false_value()
    {
        Composer::shouldReceive('isInstalled')->with('statamic/forms-pro')->andReturn(true);

        $this->setTestRoles(['test' => ['access cp', 'configure forms']]);
        $user = tap(User::make()->assignRole('test'))->save();

        $form = tap(Form::make('test')->formFields([
            'pages' => [
                ['id' => 'page1', 'sections' => [['display' => 'Section', 'fields' => []]]],
                ['id' => 'page2', 'sections' => [['display' => 'Section', 'fields' => []]]],
            ],
        ]))->save();

        $payload = [
            'pages' => [
                [
                    '_id' => 'page1',
                    'rules' => [
                        [
                            '_id' => 'rule1',
                            'conditions' => [['_id' => 'cond1', 'field' => 'subscribed', 'operator' => 'equals', 'value' => false]],
                            'destination' => 'page2',
                        ],
                    ],
                    'sections' => [['display' => 'Section', 'fields' => []]],
                ],
                ['_id' => 'page2', 'sections' => [['display' => 'Section', 'fields' => []]]],
            ],
        ];

        $this
            ->actingAs($user)
            ->patch(cp_route('forms.logic.update', $form->handle()), $payload)
            ->assertSuccessful();

        $conditions = Form::find('test')->formFields()->pages()[0]['rules'][0]['conditions'];

        $this->assertCount(1, $conditions);
        $this->assertFalse($conditions[0]['value']);
    }

    #[Test]
    public function it_denies_update_without_permission()
    {
        $this->setTestRoles(['test' => ['access cp']]);
        $user = tap(User::make()->assignRole('test'))->save();
        $form = tap(Form::make('test'))->save();

        $this
            ->from('/original')
            ->actingAs($user)
            ->patch(cp_route('forms.logic.update', $form->handle()), ['pages' => []])
            ->assertRedirect('/original')
            ->assertSessionHas('error');
    }
}

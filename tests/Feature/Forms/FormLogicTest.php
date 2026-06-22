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
        $user = User::make()->assignRole('test')->save();
        $form = tap(Form::make('test'))->save();

        $this
            ->actingAs($user)
            ->get(cp_route('forms.logic.edit', $form->handle()))
            ->assertSuccessful()
            ->assertInertia(fn ($page) => $page
                ->component('forms/Logic')
                ->has('form')
                ->has('pages')
                ->has('fields')
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
            ->get(cp_route('forms.logic.edit', $form->handle()))
            ->assertRedirect('/original')
            ->assertSessionHas('error');
    }

    #[Test]
    public function it_provides_pages_with_rules()
    {
        Composer::shouldReceive('isInstalled')->with('statamic/forms-pro')->andReturn(true);

        $this->setTestRoles(['test' => ['access cp', 'configure forms']]);
        $user = User::make()->assignRole('test')->save();

        $form = tap(Form::make('test')->formFields([
            'pages' => [
                [
                    'id' => 'page1',
                    'display' => 'First Page',
                    'rules' => [
                        [
                            'conditions' => [
                                ['field' => 'name', 'operator' => 'equals', 'value' => 'test'],
                            ],
                            'destination' => 'page2',
                        ],
                    ],
                    'sections' => [
                        ['display' => 'Section', 'fields' => []],
                    ],
                ],
                [
                    'id' => 'page2',
                    'display' => 'Second Page',
                    'sections' => [
                        ['display' => 'Section', 'fields' => []],
                    ],
                ],
            ],
        ]))->save();

        $this
            ->actingAs($user)
            ->get(cp_route('forms.logic.edit', $form->handle()))
            ->assertSuccessful()
            ->assertInertia(fn ($page) => $page
                ->component('forms/Logic')
                ->has('pages', 2)
                ->where('pages.0._id', 'page1')
                ->where('pages.0.display', 'First Page')
                ->has('pages.0.rules', 1)
                ->has('pages.0.rules.0._id')
                ->has('pages.0.rules.0.conditions.0._id')
                ->where('pages.0.rules.0.destination', 'page2')
            );
    }

    #[Test]
    public function it_provides_fields_with_conditions()
    {
        Composer::shouldReceive('isInstalled')->with('statamic/forms-pro')->andReturn(true);

        $this->setTestRoles(['test' => ['access cp', 'configure forms']]);
        $user = User::make()->assignRole('test')->save();

        $form = tap(Form::make('test')->formFields([
            'pages' => [
                [
                    'sections' => [
                        [
                            'display' => 'Section',
                            'fields' => [
                                [
                                    'handle' => 'name',
                                    'field' => [
                                        'type' => 'short_answer',
                                        'display' => 'Name',
                                    ],
                                ],
                                [
                                    'handle' => 'email',
                                    'field' => [
                                        'type' => 'short_answer',
                                        'display' => 'Email',
                                        'if' => ['name' => 'not empty'],
                                        'always_save' => true,
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ]))->save();

        $this
            ->actingAs($user)
            ->get(cp_route('forms.logic.edit', $form->handle()))
            ->assertSuccessful()
            ->assertInertia(fn ($page) => $page
                ->component('forms/Logic')
                ->has('fields', 2)
                ->where('fields.0._id', 'name')
                ->where('fields.0.handle', 'name')
                ->where('fields.0.display', 'Name')
                ->where('fields.0.category', 'text')
                ->where('fields.1._id', 'email')
                ->where('fields.1.if', ['name' => 'not empty'])
                ->where('fields.1.always_save', true)
            );
    }

    #[Test]
    public function it_can_update_page_rules()
    {
        Composer::shouldReceive('isInstalled')->with('statamic/forms-pro')->andReturn(true);

        $this->setTestRoles(['test' => ['access cp', 'configure forms']]);
        $user = User::make()->assignRole('test')->save();

        $form = tap(Form::make('test')->formFields([
            'pages' => [
                [
                    'id' => 'page1',
                    'sections' => [
                        ['display' => 'Section', 'fields' => []],
                    ],
                ],
                [
                    'id' => 'page2',
                    'sections' => [
                        ['display' => 'Section', 'fields' => []],
                    ],
                ],
            ],
        ]))->save();

        $payload = [
            'pages' => [
                [
                    '_id' => 'page1',
                    'rules' => [
                        [
                            '_id' => 'rule1',
                            'conditions' => [
                                [
                                    '_id' => 'cond1',
                                    'field' => 'favorite_color',
                                    'operator' => 'equals',
                                    'value' => 'blue',
                                ],
                            ],
                            'destination' => 'page2',
                        ],
                    ],
                ],
            ],
            'fields' => [],
        ];

        $this
            ->actingAs($user)
            ->patch(cp_route('forms.logic.update', $form->handle()), $payload)
            ->assertSuccessful();

        $form = Form::find('test');
        $rules = $form->formFields()->pages()[0]['rules'];

        $this->assertCount(1, $rules);
        $this->assertEquals('page2', $rules[0]['destination']);
        $this->assertCount(1, $rules[0]['conditions']);
        $this->assertEquals('favorite_color', $rules[0]['conditions'][0]['field']);
        $this->assertArrayNotHasKey('_id', $rules[0]);
        $this->assertArrayNotHasKey('_id', $rules[0]['conditions'][0]);
    }

    #[Test]
    public function it_can_update_field_conditions()
    {
        Composer::shouldReceive('isInstalled')->with('statamic/forms-pro')->andReturn(true);

        $this->setTestRoles(['test' => ['access cp', 'configure forms']]);
        $user = User::make()->assignRole('test')->save();

        $form = tap(Form::make('test')->formFields([
            'pages' => [
                [
                    'id' => 'page1',
                    'sections' => [
                        [
                            'display' => 'Section',
                            'fields' => [
                                [
                                    'handle' => 'name',
                                    'field' => [
                                        'type' => 'short_answer',
                                        'display' => 'Name',
                                    ],
                                ],
                                [
                                    'handle' => 'email',
                                    'field' => [
                                        'type' => 'short_answer',
                                        'display' => 'Email',
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ]))->save();

        $payload = [
            'pages' => [],
            'fields' => [
                [
                    '_id' => 'email',
                    'if' => ['name' => 'not empty'],
                    'always_save' => true,
                ],
            ],
        ];

        $this
            ->actingAs($user)
            ->patch(cp_route('forms.logic.update', $form->handle()), $payload)
            ->assertSuccessful();

        $form = Form::find('test');
        $fields = $form->formFields()->pages()[0]['sections'][0]['fields'];

        $this->assertCount(2, $fields);
        $this->assertArrayNotHasKey('if', $fields[0]['field']);
        $this->assertEquals(['name' => 'not empty'], $fields[1]['field']['if']);
        $this->assertTrue($fields[1]['field']['always_save']);
    }

    #[Test]
    public function it_can_update_the_hidden_state_of_a_field()
    {
        Composer::shouldReceive('isInstalled')->with('statamic/forms-pro')->andReturn(true);

        $this->setTestRoles(['test' => ['access cp', 'configure forms']]);
        $user = User::make()->assignRole('test')->save();

        $form = tap(Form::make('test')->formFields([
            'pages' => [
                [
                    'id' => 'page1',
                    'sections' => [
                        [
                            'display' => 'Section',
                            'fields' => [
                                [
                                    'handle' => 'name',
                                    'field' => [
                                        'type' => 'short_answer',
                                        'display' => 'Name',
                                        'hidden' => true,
                                    ],
                                ],
                                [
                                    'handle' => 'email',
                                    'field' => [
                                        'type' => 'short_answer',
                                        'display' => 'Email',
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ]))->save();

        // The hidden state should be provided when loading the logic page.
        $this
            ->actingAs($user)
            ->get(cp_route('forms.logic.edit', $form->handle()))
            ->assertSuccessful()
            ->assertInertia(fn ($page) => $page
                ->where('fields.0.hidden', true)
                ->where('fields.1.hidden', false)
            );

        $payload = [
            'pages' => [],
            'fields' => [
                ['_id' => 'name', 'hidden' => false],
                ['_id' => 'email', 'hidden' => true],
            ],
        ];

        $this
            ->actingAs($user)
            ->patch(cp_route('forms.logic.update', $form->handle()), $payload)
            ->assertSuccessful();

        $fields = Form::find('test')->formFields()->pages()[0]['sections'][0]['fields'];

        // The now-visible field shouldn't persist `hidden` since false is the default.
        $this->assertArrayNotHasKey('hidden', $fields[0]['field']);

        // The now-hidden field should persist `hidden: true`.
        $this->assertTrue($fields[1]['field']['hidden']);
    }

    #[Test]
    public function it_denies_update_without_permission()
    {
        $this->setTestRoles(['test' => ['access cp']]);
        $user = tap(User::make()->assignRole('test'))->save();
        $form = tap(Form::make('test'))->save();

        $payload = [
            'pages' => [],
            'fields' => [],
        ];

        $this
            ->from('/original')
            ->actingAs($user)
            ->patch(cp_route('forms.logic.update', $form->handle()), $payload)
            ->assertRedirect('/original')
            ->assertSessionHas('error');
    }
}

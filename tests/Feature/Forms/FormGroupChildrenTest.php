<?php

namespace Tests\Feature\Forms;

use PHPUnit\Framework\Attributes\Test;
use Statamic\Facades\Form;
use Statamic\Facades\FormConnector;
use Statamic\Facades\User;
use Tests\FakesRoles;
use Tests\PreventSavingStacheItemsToDisk;
use Tests\TestCase;

class FormGroupChildrenTest extends TestCase
{
    use FakesRoles;
    use PreventSavingStacheItemsToDisk;

    protected function resolveApplicationConfiguration($app)
    {
        parent::resolveApplicationConfiguration($app);

        $app['config']['statamic.forms.forms'] = $this->fakeStacheDirectory.'/forms';
    }

    #[Test]
    public function it_generates_a_fake_submission_for_a_group_with_form_fieldtype_children()
    {
        $form = $this->makeForm();

        $this
            ->actingAs(tap(User::make()->makeSuper())->save())
            ->post(cp_route('forms.submissions.generate-fake', $form->handle()), ['mode' => 'cp_only'])
            ->assertOk();

        $address = $form->querySubmissions()->first()->get('address');

        $this->assertIsString($address['city']);
        $this->assertMatchesRegularExpression('/^.+@.+\..+$/', $address['contact']);
    }

    #[Test]
    public function it_previews_an_email_for_a_group_with_form_fieldtype_children()
    {
        FormConnector::routes();

        $form = $this->makeForm();

        $this
            ->actingAs(tap(User::make()->makeSuper())->save())
            ->postJson(cp_route('forms.connect.email.preview', $form->handle()), [
                'id' => 'abc',
                'to' => ['team@example.com'],
                'subject' => 'Hello',
            ])
            ->assertOk();
    }

    #[Test]
    public function it_shows_a_submission_for_a_group_with_form_fieldtype_children()
    {
        $form = $this->makeForm();

        $submission = tap($form->makeSubmission()->data([
            'name' => 'Bob',
            'address' => ['city' => 'Paris', 'contact' => 'bob@example.com'],
        ]))->save();

        $this
            ->actingAs(tap(User::make()->makeSuper())->save())
            ->get(cp_route('forms.submissions.show', [$form->handle(), $submission->id()]))
            ->assertOk();
    }

    #[Test]
    public function it_returns_group_children_as_form_fields_for_editing()
    {
        $form = $this->makeForm();

        $response = $this
            ->actingAs($this->formEditor())
            ->post(cp_route('forms.builder.fields.edit', $form->handle()), [
                'type' => 'group',
                'values' => [
                    'display' => 'Address',
                    'handle' => 'address',
                    'fields' => [
                        ['handle' => 'city', 'field' => ['type' => 'short_answer', 'display' => 'City']],
                    ],
                ],
            ])
            ->assertOk();

        $child = $response->json('values.fields.0');

        $this->assertEquals('city', $child['handle']);
        $this->assertEquals('inline', $child['type']);
        $this->assertEquals('short_answer', $child['fieldtype']);
        $this->assertEquals('City', $child['config']['display']);
    }

    #[Test]
    public function it_saves_group_children_as_form_fields()
    {
        $form = $this->makeForm();

        $response = $this
            ->actingAs($this->formEditor())
            ->post(cp_route('forms.builder.fields.update', $form->handle()), [
                'type' => 'group',
                'values' => [
                    'display' => 'Address',
                    'handle' => 'address',
                    'fields' => [
                        [
                            '_id' => 'one',
                            'handle' => 'city',
                            'type' => 'inline',
                            'fieldtype' => 'short_answer',
                            'config' => ['type' => 'short_answer', 'display' => 'City', 'placeholder' => 'Paris', 'width' => 100],
                        ],
                        [
                            '_id' => 'two',
                            'handle' => 'subscribe',
                            'type' => 'inline',
                            'fieldtype' => 'toggle',
                            'config' => ['type' => 'toggle', 'display' => 'Subscribe', 'inline_label' => 'Yes please'],
                        ],
                    ],
                ],
            ])
            ->assertOk();

        $this->assertEquals([
            ['handle' => 'city', 'field' => ['type' => 'short_answer', 'display' => 'City', 'placeholder' => 'Paris']],
            ['handle' => 'subscribe', 'field' => ['type' => 'toggle', 'display' => 'Subscribe', 'inline_label' => 'Yes please']],
        ], $response->json('values.fields'));
    }

    #[Test]
    public function it_validates_group_child_handles_when_committing_a_child()
    {
        $form = $this->makeForm();

        $this
            ->actingAs($this->formEditor())
            ->postJson(cp_route('forms.builder.fields.update', $form->handle()), [
                'type' => 'short_answer',
                'id' => 'two',
                'values' => ['display' => 'City', 'handle' => 'city'],
                'fields' => [
                    ['_id' => 'one', 'type' => 'inline', 'handle' => 'city', 'config' => ['type' => 'short_answer']],
                    ['_id' => 'two', 'type' => 'inline', 'handle' => 'town', 'config' => ['type' => 'short_answer']],
                ],
            ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['handle']);

        $this
            ->actingAs($this->formEditor())
            ->postJson(cp_route('forms.builder.fields.update', $form->handle()), [
                'type' => 'short_answer',
                'id' => 'two',
                'values' => ['display' => 'City', 'handle' => 'not a handle'],
                'fields' => [],
            ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['handle']);

        $this
            ->actingAs($this->formEditor())
            ->postJson(cp_route('forms.builder.fields.update', $form->handle()), [
                'type' => 'short_answer',
                'id' => 'two',
                'values' => ['display' => 'Town', 'handle' => 'town'],
                'fields' => [
                    ['_id' => 'one', 'type' => 'inline', 'handle' => 'city', 'config' => ['type' => 'short_answer']],
                    ['_id' => 'two', 'type' => 'inline', 'handle' => 'town', 'config' => ['type' => 'short_answer']],
                ],
            ])
            ->assertOk();
    }

    #[Test]
    public function group_children_can_use_handles_that_are_only_reserved_at_the_top_level_of_a_form()
    {
        $form = $this->makeForm();

        foreach (['message', 'messages', 'date'] as $handle) {
            $this
                ->actingAs($this->formEditor())
                ->postJson(cp_route('forms.builder.fields.update', $form->handle()), [
                    'type' => 'long_answer',
                    'id' => 'one',
                    'values' => ['display' => 'Message', 'handle' => $handle],
                    'fields' => [['_id' => 'one', 'type' => 'inline', 'handle' => $handle, 'config' => ['type' => 'long_answer']]],
                ])
                ->assertOk();
        }

        $this
            ->actingAs($this->formEditor())
            ->postJson(cp_route('forms.builder.fields.update', $form->handle()), [
                'type' => 'short_answer',
                'id' => 'one',
                'values' => ['display' => 'Status', 'handle' => 'status'],
                'fields' => [],
            ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['handle']);
    }

    #[Test]
    public function it_doesnt_validate_handles_when_updating_a_top_level_field_preview()
    {
        $form = $this->makeForm();

        $this
            ->actingAs($this->formEditor())
            ->postJson(cp_route('forms.builder.fields.update', $form->handle()), [
                'type' => 'short_answer',
                'values' => ['display' => 'City', 'handle' => ''],
            ])
            ->assertOk();
    }

    #[Test]
    public function the_builder_saves_a_group_with_form_fieldtype_children()
    {
        $form = $this->makeForm();

        $this
            ->actingAs($this->formEditor())
            ->patchJson(cp_route('forms.builder.update', $form->handle()), [
                'pages' => [[
                    '_id' => 'main',
                    'sections' => [[
                        '_id' => 'one',
                        'fields' => [[
                            '_id' => 'a',
                            'handle' => 'address',
                            'type' => 'inline',
                            'fieldtype' => 'group',
                            'config' => [
                                'type' => 'group',
                                'display' => 'Address',
                                'fields' => [
                                    ['handle' => 'city', 'field' => ['type' => 'short_answer', 'display' => 'City']],
                                ],
                            ],
                        ]],
                    ]],
                ]],
            ])
            ->assertOk();

        $this->assertEquals(
            [['handle' => 'city', 'field' => ['type' => 'short_answer', 'display' => 'City']]],
            Form::find('contact')->formFields()->field('address')->config()['fields']
        );
    }

    private function makeForm()
    {
        return tap(Form::make('contact')->formFields([
            'fields' => [
                ['handle' => 'name', 'field' => ['type' => 'short_answer', 'display' => 'Name']],
                ['handle' => 'address', 'field' => [
                    'type' => 'group',
                    'display' => 'Address',
                    'fields' => [
                        ['handle' => 'city', 'field' => ['type' => 'short_answer', 'display' => 'City']],
                        ['handle' => 'contact', 'field' => ['type' => 'email', 'display' => 'Contact']],
                    ],
                ]],
            ],
        ]))->save();
    }

    private function formEditor()
    {
        $this->setTestRoles(['test' => ['access cp', 'edit contact form']]);

        return tap(User::make()->assignRole('test'))->save();
    }
}

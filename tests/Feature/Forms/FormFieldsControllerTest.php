<?php

namespace Tests\Feature\Forms;

use PHPUnit\Framework\Attributes\Test;
use Statamic\Facades\Form;
use Statamic\Facades\User;
use Tests\FakesRoles;
use Tests\PreventSavingStacheItemsToDisk;
use Tests\TestCase;

class FormFieldsControllerTest extends TestCase
{
    use FakesRoles;
    use PreventSavingStacheItemsToDisk;

    #[Test]
    public function it_returns_field_config_for_editing()
    {
        $this->setTestRoles(['test' => ['access cp', 'edit test form']]);
        $user = User::make()->assignRole('test')->save();
        $form = tap(Form::make('test'))->save();

        $response = $this
            ->actingAs($user)
            ->post(cp_route('forms.builder.fields.edit', $form->handle()), [
                'type' => 'short_answer',
                'values' => [
                    'display' => 'My Field',
                    'instructions' => 'Enter your answer',
                ],
            ]);

        $response->assertSuccessful();
        $response->assertJsonStructure([
            'fieldtype',
            'blueprint',
            'values',
            'meta',
        ]);

        $data = $response->json();

        // Fieldtype information
        $this->assertEquals('short_answer', $data['fieldtype']['handle']);
        $this->assertArrayHasKey('title', $data['fieldtype']);
        $this->assertArrayHasKey('icon', $data['fieldtype']);

        // Values are preprocessed
        $this->assertEquals('My Field', $data['values']['display']);
        $this->assertEquals('Enter your answer', $data['values']['instructions']);

        // Blueprint structure with common field options
        $this->assertArrayHasKey('tabs', $data['blueprint']);
        $fieldHandles = collect($data['blueprint']['tabs'])
            ->flatMap(fn ($tab) => collect($tab['sections'])
                ->flatMap(fn ($section) => collect($section['fields'])
                    ->pluck('handle')))
            ->toArray();
        $this->assertContains('display', $fieldHandles);
        $this->assertContains('instructions', $fieldHandles);
    }

    #[Test]
    public function it_requires_type_parameter_for_edit()
    {
        $this->setTestRoles(['test' => ['access cp', 'edit test form']]);
        $user = User::make()->assignRole('test')->save();
        $form = tap(Form::make('test'))->save();

        $this
            ->actingAs($user)
            ->post(cp_route('forms.builder.fields.edit', $form->handle()), [
                'values' => [],
            ])
            ->assertSessionHasErrors(['type']);
    }

    #[Test]
    public function it_processes_field_config_on_update()
    {
        $this->setTestRoles(['test' => ['access cp', 'edit test form']]);
        $user = User::make()->assignRole('test')->save();
        $form = tap(Form::make('test'))->save();

        $response = $this
            ->actingAs($user)
            ->post(cp_route('forms.builder.fields.update', $form->handle()), [
                'type' => 'short_answer',
                'values' => [
                    'display' => 'My Field',
                    'instructions' => 'Enter your answer',
                    'placeholder' => 'Type here',
                ],
            ]);

        $response->assertSuccessful();
        $response->assertJsonStructure([
            'values',
            'preview',
        ]);

        $data = $response->json();

        // Values are processed
        $this->assertEquals('My Field', $data['values']['display']);
        $this->assertEquals('Enter your answer', $data['values']['instructions']);
        $this->assertEquals('Type here', $data['values']['placeholder']);

        // Preview structure
        $this->assertArrayHasKey('config', $data['preview']);
        $this->assertArrayHasKey('value', $data['preview']);
        $this->assertArrayHasKey('meta', $data['preview']);
    }

    #[Test]
    public function it_requires_type_parameter_for_update()
    {
        $this->setTestRoles(['test' => ['access cp', 'edit test form']]);
        $user = User::make()->assignRole('test')->save();
        $form = tap(Form::make('test'))->save();

        $this
            ->actingAs($user)
            ->post(cp_route('forms.builder.fields.update', $form->handle()), [
                'values' => ['display' => 'Test'],
            ])
            ->assertSessionHasErrors(['type']);
    }

    #[Test]
    public function it_requires_values_parameter_for_update()
    {
        $this->setTestRoles(['test' => ['access cp', 'edit test form']]);
        $user = User::make()->assignRole('test')->save();
        $form = tap(Form::make('test'))->save();

        $this
            ->actingAs($user)
            ->post(cp_route('forms.builder.fields.update', $form->handle()), [
                'type' => 'short_answer',
            ])
            ->assertSessionHasErrors(['values']);
    }

    #[Test]
    public function it_returns_preview_with_field_config()
    {
        $this->setTestRoles(['test' => ['access cp', 'edit test form']]);
        $user = User::make()->assignRole('test')->save();
        $form = tap(Form::make('test'))->save();

        $response = $this
            ->actingAs($user)
            ->post(cp_route('forms.builder.fields.update', $form->handle()), [
                'type' => 'short_answer',
                'values' => [
                    'display' => 'Favorite Color',
                    'placeholder' => 'Enter a color',
                ],
            ]);

        $response->assertSuccessful();
        $preview = $response->json('preview');

        $this->assertEquals('Favorite Color', $preview['config']['display']);
        $this->assertEquals('Enter a color', $preview['config']['placeholder']);
    }
}

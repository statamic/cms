<?php

namespace Tests\Feature\Forms;

use PHPUnit\Framework\Attributes\Test;
use Statamic\Facades\Fieldset;
use Statamic\Facades\Form;
use Statamic\Facades\User;
use Tests\FakesRoles;
use Tests\PreventSavingStacheItemsToDisk;
use Tests\TestCase;

class FormFieldsetPreviewsControllerTest extends TestCase
{
    use FakesRoles, PreventSavingStacheItemsToDisk;

    #[Test]
    public function it_returns_field_previews_for_a_fieldset()
    {
        $this->setTestRoles(['test' => ['access cp', 'edit test form']]);
        $user = User::make()->assignRole('test')->save();
        $form = tap(Form::make('test'))->save();

        $fieldset = Fieldset::make('contact')->setContents([
            'fields' => [
                ['handle' => 'name', 'field' => ['type' => 'text', 'display' => 'Name']],
                ['handle' => 'email', 'field' => ['type' => 'text', 'display' => 'Email', 'input_type' => 'email']],
            ],
        ]);
        Fieldset::shouldReceive('find')->with('contact')->andReturn($fieldset);

        $this
            ->actingAs($user)
            ->post(cp_route('forms.builder.fieldset-previews', $form->handle()), [
                'fieldset' => 'contact',
            ])
            ->assertSuccessful()
            ->assertJsonStructure([
                'previews' => [
                    'name' => ['config', 'value', 'meta'],
                    'email' => ['config', 'value', 'meta'],
                ],
            ])
            ->assertJsonPath('previews.name.config.display', 'Name')
            ->assertJsonPath('previews.email.config.display', 'Email')
            ->assertJsonPath('previews.email.config.input_type', 'email');
    }

    #[Test]
    public function it_requires_fieldset_parameter()
    {
        $this->setTestRoles(['test' => ['access cp', 'edit test form']]);
        $user = User::make()->assignRole('test')->save();
        $form = tap(Form::make('test'))->save();

        $this
            ->actingAs($user)
            ->post(cp_route('forms.builder.fieldset-previews', $form->handle()), [])
            ->assertSessionHasErrors(['fieldset']);
    }

    #[Test]
    public function it_returns_empty_previews_for_nonexistent_fieldset()
    {
        $this->setTestRoles(['test' => ['access cp', 'edit test form']]);
        $user = User::make()->assignRole('test')->save();
        $form = tap(Form::make('test'))->save();

        Fieldset::shouldReceive('find')->with('nonexistent')->andReturn(null);

        $this
            ->actingAs($user)
            ->post(cp_route('forms.builder.fieldset-previews', $form->handle()), [
                'fieldset' => 'nonexistent',
            ])
            ->assertSuccessful()
            ->assertJson(['previews' => []]);
    }

    #[Test]
    public function it_handles_fields_that_fail_to_generate_previews()
    {
        $this->setTestRoles(['test' => ['access cp', 'edit test form']]);
        $user = User::make()->assignRole('test')->save();
        $form = tap(Form::make('test'))->save();

        $fieldset = Fieldset::make('mixed')->setContents([
            'fields' => [
                ['handle' => 'name', 'field' => ['type' => 'text', 'display' => 'Name']],
                ['handle' => 'broken', 'field' => ['type' => 'nonexistent_fieldtype']],
            ],
        ]);
        Fieldset::shouldReceive('find')->with('mixed')->andReturn($fieldset);

        $this
            ->actingAs($user)
            ->post(cp_route('forms.builder.fieldset-previews', $form->handle()), [
                'fieldset' => 'mixed',
            ])
            ->assertSuccessful()
            ->assertJsonStructure(['previews' => ['name' => ['config', 'value', 'meta']]])
            ->assertJsonPath('previews.broken', null);
    }
}

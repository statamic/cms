<?php

namespace Tests\Feature\Forms;

use Illuminate\Support\Facades\Log;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Facades\Form;
use Statamic\Facades\User;
use Tests\FakesRoles;
use Tests\PreventSavingStacheItemsToDisk;
use Tests\TestCase;

class EditFormTest extends TestCase
{
    use FakesRoles;
    use PreventSavingStacheItemsToDisk;

    protected function resolveApplicationConfiguration($app)
    {
        parent::resolveApplicationConfiguration($app);

        $app['config']['statamic.forms.forms'] = $this->fakeStacheDirectory.'/forms';
    }

    #[Test]
    public function it_shows_the_edit_page_if_you_have_permission()
    {
        $this->setTestRoles(['test' => ['access cp', 'configure forms']]);
        $user = User::make()->assignRole('test')->save();
        $form = tap(Form::make('test'))->save();

        $this
            ->actingAs($user)
            ->get(cp_route('forms.edit', $form->handle()))
            ->assertSuccessful()
            ->assertInertia(fn ($page) => $page->component('forms/Edit'));
    }

    #[Test]
    public function it_shows_the_edit_page_with_the_edit_forms_permission()
    {
        $this->setTestRoles(['test' => ['access cp', 'edit forms']]);
        $user = User::make()->assignRole('test')->save();
        $form = tap(Form::make('test'))->save();

        $this
            ->actingAs($user)
            ->get(cp_route('forms.edit', $form->handle()))
            ->assertSuccessful()
            ->assertInertia(fn ($page) => $page->component('forms/Edit'));
    }

    #[Test]
    public function it_shows_the_edit_page_with_the_edit_form_permission()
    {
        $this->setTestRoles(['test' => ['access cp', 'edit test form']]);
        $user = User::make()->assignRole('test')->save();
        $form = tap(Form::make('test'))->save();

        $this
            ->actingAs($user)
            ->get(cp_route('forms.edit', $form->handle()))
            ->assertSuccessful()
            ->assertInertia(fn ($page) => $page->component('forms/Edit'));
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
            ->get(cp_route('forms.edit', $form->handle()))
            ->assertRedirect('/original')
            ->assertSessionHas('error');
    }

    #[Test]
    public function it_denies_access_with_only_the_configure_form_fields_permission()
    {
        $this->setTestRoles(['test' => ['access cp', 'configure form fields']]);
        $user = tap(User::make()->assignRole('test'))->save();
        $form = tap(Form::make('test'))->save();

        $this
            ->from('/original')
            ->actingAs($user)
            ->get(cp_route('forms.edit', $form->handle()))
            ->assertRedirect('/original')
            ->assertSessionHas('error');
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
            ->get(cp_route('forms.edit', $form->handle()))
            ->assertRedirect('/original')
            ->assertSessionHas('error');
    }

    #[Test]
    public function fields_can_be_added()
    {
        $this->setTestRoles(['test' => ['access cp', 'configure forms']]);
        $user = User::make()->assignRole('test')->save();
        $form = tap(Form::make('test'))->save();

        Form::appendConfigFields('*', 'Honeypot', [
            'a' => ['type' => 'text', 'display' => 'First injected into honeypot section'],
            'b' => ['type' => 'text', 'display' => 'Second injected into honeypot section'],
        ]);
        Form::appendConfigFields('*', 'Additional Section', [
            'c' => ['type' => 'text', 'display' => 'First injected into additional section'],
            'd' => ['type' => 'text', 'display' => 'Second injected into additional section'],
        ]);

        $this
            ->actingAs($user)
            ->get(cp_route('forms.edit', $form->handle()))
            ->assertSuccessful()
            ->assertSeeInOrder([
                'Title',
                'Honeypot',
                'First injected into honeypot section',
                'Second injected into honeypot section',
                'Store Submissions',
                'Additional Section',
                'First injected into additional section',
                'Second injected into additional section',
            ]);
    }

    #[Test]
    public function section_positions_cannot_be_used_when_adding_fields_to_an_existing_section()
    {
        $this->setTestRoles(['test' => ['access cp', 'configure forms']]);
        $user = User::make()->assignRole('test')->save();
        $form = tap(Form::make('test'))->save();

        Form::appendConfigFields('*', 'Fields', [
            'recaptcha' => ['type' => 'text'],
        ], afterSection: 'name');

        $this->withoutExceptionHandling();
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage("The [Fields] section already exists, so beforeSection and afterSection can't be used.");

        $this
            ->actingAs($user)
            ->get(cp_route('forms.edit', $form->handle()));
    }

    #[Test]
    public function sections_can_be_added_before_an_existing_section()
    {
        $this->setTestRoles(['test' => ['access cp', 'configure forms']]);
        $user = User::make()->assignRole('test')->save();
        $form = tap(Form::make('test'))->save();

        Form::appendConfigFields('*', 'Automagic Forms', [
            'automagic_form' => ['type' => 'toggle', 'display' => 'Enable Automagic Form'],
        ], beforeSection: 'submissions');

        $this
            ->actingAs($user)
            ->get(cp_route('forms.edit', $form->handle()))
            ->assertSuccessful()
            ->assertSeeInOrder([
                'Honeypot',
                'Automagic Forms',
                'Enable Automagic Form',
                'Store Submissions',
            ]);
    }

    #[Test]
    public function sections_can_be_added_after_an_existing_section()
    {
        $this->setTestRoles(['test' => ['access cp', 'configure forms']]);
        $user = User::make()->assignRole('test')->save();
        $form = tap(Form::make('test'))->save();

        Form::appendConfigFields('*', 'Automagic Forms', [
            'automagic_form' => ['type' => 'toggle', 'display' => 'Enable Automagic Form'],
        ], afterSection: 'fields');

        $this
            ->actingAs($user)
            ->get(cp_route('forms.edit', $form->handle()))
            ->assertSuccessful()
            ->assertSeeInOrder([
                'Honeypot',
                'Automagic Forms',
                'Enable Automagic Form',
                'Store Submissions',
            ]);
    }

    #[Test]
    public function sections_are_appended_and_a_warning_is_logged_when_the_target_section_does_not_exist()
    {
        $this->setTestRoles(['test' => ['access cp', 'configure forms']]);
        $user = User::make()->assignRole('test')->save();
        $form = tap(Form::make('test'))->save();

        Log::shouldReceive('warning')
            ->once()
            ->with('Form config section [automagic_forms] could not be placed relative to [nope] because it does not exist. Appending it instead.');

        Form::appendConfigFields('*', 'Automagic Forms', [
            'automagic_form' => ['type' => 'toggle', 'display' => 'Enable Automagic Form'],
        ], afterSection: 'nope');

        $this
            ->actingAs($user)
            ->get(cp_route('forms.edit', $form->handle()))
            ->assertSuccessful()
            ->assertSeeInOrder([
                'Store Submissions',
                'Close Date',
                'Automagic Forms',
                'Enable Automagic Form',
            ]);
    }

    #[Test]
    public function sections_replace_existing_sections_with_the_same_handle()
    {
        $this->setTestRoles(['test' => ['access cp', 'configure forms']]);
        $user = User::make()->assignRole('test')->save();
        $form = tap(Form::make('test'))->save();

        Form::appendConfigFields('*', 'fields', [
            'injected' => ['type' => 'text', 'display' => 'Injected into duplicate section'],
        ]);

        $this
            ->actingAs($user)
            ->get(cp_route('forms.edit', $form->handle()))
            ->assertSuccessful()
            ->assertSeeInOrder(['Title', 'Injected into duplicate section', 'Store Submissions']);
    }

    #[Test]
    public function positioned_sections_replace_existing_sections_with_the_same_handle_in_place_and_a_warning_is_logged()
    {
        $this->setTestRoles(['test' => ['access cp', 'configure forms']]);
        $user = User::make()->assignRole('test')->save();
        $form = tap(Form::make('test'))->save();

        Log::shouldReceive('warning')
            ->once()
            ->with('Form config section [fields] replaces an existing section, so its position was ignored.');

        Form::appendConfigFields('*', 'fields', [
            'injected' => ['type' => 'text', 'display' => 'Injected into duplicate section'],
        ], afterSection: 'submissions');

        $this
            ->actingAs($user)
            ->get(cp_route('forms.edit', $form->handle()))
            ->assertSuccessful()
            ->assertSeeInOrder(['Title', 'Injected into duplicate section', 'Store Submissions']);
    }
}

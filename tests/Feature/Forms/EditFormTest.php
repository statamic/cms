<?php

namespace Tests\Feature\Forms;

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

        Form::appendConfigFields('*', 'Fields', [
            'a' => ['type' => 'text', 'display' => 'First injected into fields section'],
            'b' => ['type' => 'text', 'display' => 'Second injected into fields section'],
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
                'First injected into fields section',
                'Second injected into fields section',
                'Store Submissions',
                'Additional Section',
                'First injected into additional section',
                'Second injected into additional section',
            ]);
    }

    #[Test]
    public function fields_can_be_added_before_an_existing_field()
    {
        $this->setTestRoles(['test' => ['access cp', 'configure forms']]);
        $user = User::make()->assignRole('test')->save();
        $form = tap(Form::make('test'))->save();

        Form::appendConfigFields('*', 'Fields', [
            'recaptcha' => ['type' => 'text', 'display' => 'Injected before honeypot'],
        ], before: 'honeypot');

        $this
            ->actingAs($user)
            ->get(cp_route('forms.edit', $form->handle()))
            ->assertSuccessful()
            ->assertSeeInOrder([
                'Title',
                'Injected before honeypot',
                'Honeypot',
                'Store Submissions',
            ]);
    }

    #[Test]
    public function fields_can_be_added_after_an_existing_field()
    {
        $this->setTestRoles(['test' => ['access cp', 'configure forms']]);
        $user = User::make()->assignRole('test')->save();
        $form = tap(Form::make('test'))->save();

        Form::appendConfigFields('*', 'Submissions', [
            'webhook' => ['type' => 'text', 'display' => 'Injected after store'],
        ], after: 'store');

        $this
            ->actingAs($user)
            ->get(cp_route('forms.edit', $form->handle()))
            ->assertSuccessful()
            ->assertSeeInOrder([
                'Store Submissions',
                'Injected after store',
                'Enable Fake Submission Generator',
            ]);
    }

    #[Test]
    public function sections_can_be_added_before_an_existing_section()
    {
        $this->setTestRoles(['test' => ['access cp', 'configure forms']]);
        $user = User::make()->assignRole('test')->save();
        $form = tap(Form::make('test'))->save();

        Form::appendConfigFields('*', 'Automagic Forms', [
            'automagic_form' => ['type' => 'toggle', 'display' => 'Enable Automagic Form'],
        ], before: 'submissions');

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
        ], after: 'fields');

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
}

<?php

namespace Tests\Feature\Forms;

use PHPUnit\Framework\Attributes\Test;
use Statamic\Facades\Form;
use Statamic\Facades\FormSubmission;
use Statamic\Facades\User;
use Tests\FakesRoles;
use Tests\PreventSavingStacheItemsToDisk;
use Tests\TestCase;

class ShowFormTest extends TestCase
{
    use FakesRoles;
    use PreventSavingStacheItemsToDisk;

    protected function resolveApplicationConfiguration($app)
    {
        parent::resolveApplicationConfiguration($app);

        $app['config']['statamic.forms.forms'] = $this->fakeStacheDirectory.'/forms';
    }

    #[Test]
    public function it_redirects_to_the_builder_when_there_are_no_submissions()
    {
        $this->setTestRoles(['test' => ['access cp', 'configure forms']]);
        $user = tap(User::make()->assignRole('test'))->save();
        $form = tap(Form::make('test'))->save();

        $this
            ->actingAs($user)
            ->get(cp_route('forms.show', $form->handle()))
            ->assertRedirect(cp_route('forms.builder.edit', $form->handle()));
    }

    #[Test]
    public function it_redirects_to_submissions_when_there_are_submissions()
    {
        $this->setTestRoles(['test' => ['access cp', 'configure forms']]);
        $user = tap(User::make()->assignRole('test'))->save();
        $form = tap(Form::make('test'))->save();
        FormSubmission::make()->form($form)->data(['foo' => 'bar'])->save();

        $this
            ->actingAs($user)
            ->get(cp_route('forms.show', $form->handle()))
            ->assertRedirect(cp_route('forms.submissions.index', $form->handle()));
    }

    #[Test]
    public function it_redirects_users_with_only_submission_permissions_to_submissions()
    {
        $this->setTestRoles(['test' => ['access cp', 'view test form submissions']]);
        $user = tap(User::make()->assignRole('test'))->save();
        $form = tap(Form::make('test'))->save();

        $this
            ->actingAs($user)
            ->get(cp_route('forms.show', $form->handle()))
            ->assertRedirect(cp_route('forms.submissions.index', $form->handle()));
    }

    #[Test]
    public function it_redirects_users_without_submission_permissions_to_the_builder()
    {
        $this->setTestRoles(['test' => ['access cp', 'edit forms']]);
        $user = tap(User::make()->assignRole('test'))->save();
        $form = tap(Form::make('test'))->save();
        FormSubmission::make()->form($form)->data(['foo' => 'bar'])->save();

        $this
            ->actingAs($user)
            ->get(cp_route('forms.show', $form->handle()))
            ->assertRedirect(cp_route('forms.builder.edit', $form->handle()));
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
            ->get(cp_route('forms.show', $form->handle()))
            ->assertRedirect('/original')
            ->assertSessionHas('error');
    }
}

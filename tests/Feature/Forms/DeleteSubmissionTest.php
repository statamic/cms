<?php

namespace Tests\Feature\Forms;

use PHPUnit\Framework\Attributes\Test;
use Statamic\Facades\Form;
use Statamic\Facades\FormSubmission;
use Statamic\Facades\User;
use Tests\FakesRoles;
use Tests\PreventSavingStacheItemsToDisk;
use Tests\TestCase;

class DeleteSubmissionTest extends TestCase
{
    use FakesRoles;
    use PreventSavingStacheItemsToDisk;

    protected function resolveApplicationConfiguration($app)
    {
        parent::resolveApplicationConfiguration($app);

        $app['config']['statamic.forms.forms'] = $this->fakeStacheDirectory.'/forms';
    }

    #[Test]
    public function it_denies_access_if_you_dont_have_permission()
    {
        $this->setTestRoles(['test' => ['access cp']]);
        $user = tap(User::make()->assignRole('test'))->save();

        $form = tap(Form::make('test'))->save();
        $submission = tap(FormSubmission::make()->form($form)->data(['foo' => 'bar']))->save();

        $this
            ->from('/original')
            ->actingAs($user)
            ->delete(cp_route('forms.submissions.destroy', [$form->handle(), $submission->id()]))
            ->assertRedirect('/original')
            ->assertSessionHas('error');

        $this->assertCount(1, $form->submissions());
    }

    #[Test]
    public function it_denies_access_with_only_the_view_form_submissions_permission()
    {
        $this->setTestRoles(['test' => ['access cp', 'view form submissions']]);
        $user = tap(User::make()->assignRole('test'))->save();

        $form = tap(Form::make('test'))->save();
        $submission = tap(FormSubmission::make()->form($form)->data(['foo' => 'bar']))->save();

        $this
            ->from('/original')
            ->actingAs($user)
            ->delete(cp_route('forms.submissions.destroy', [$form->handle(), $submission->id()]))
            ->assertRedirect('/original')
            ->assertSessionHas('error');

        $this->assertCount(1, $form->submissions());
    }

    #[Test]
    public function it_deletes_the_submission()
    {
        $this->setTestRoles(['test' => ['access cp', 'configure forms']]);
        $user = tap(User::make()->assignRole('test'))->save();

        $form = tap(Form::make('test'))->save();
        $submission = tap(FormSubmission::make()->form($form)->data(['foo' => 'bar']))->save();

        $this
            ->actingAs($user)
            ->delete(cp_route('forms.submissions.destroy', [$form->handle(), $submission->id()]))
            ->assertNoContent();

        $this->assertCount(0, $form->submissions());
    }

    #[Test]
    public function it_deletes_the_submission_with_the_delete_form_submissions_permission()
    {
        $this->setTestRoles(['test' => ['access cp', 'view form submissions', 'delete form submissions']]);
        $user = tap(User::make()->assignRole('test'))->save();

        $form = tap(Form::make('test'))->save();
        $submission = tap(FormSubmission::make()->form($form)->data(['foo' => 'bar']))->save();

        $this
            ->actingAs($user)
            ->delete(cp_route('forms.submissions.destroy', [$form->handle(), $submission->id()]))
            ->assertNoContent();

        $this->assertCount(0, $form->submissions());
    }

    #[Test]
    public function it_deletes_the_submission_with_the_per_form_delete_submissions_permission()
    {
        $this->setTestRoles(['test' => ['access cp', 'view test form submissions', 'delete test form submissions']]);
        $user = tap(User::make()->assignRole('test'))->save();

        $form = tap(Form::make('test'))->save();
        $submission = tap(FormSubmission::make()->form($form)->data(['foo' => 'bar']))->save();

        $this
            ->actingAs($user)
            ->delete(cp_route('forms.submissions.destroy', [$form->handle(), $submission->id()]))
            ->assertNoContent();

        $this->assertCount(0, $form->submissions());
    }
}

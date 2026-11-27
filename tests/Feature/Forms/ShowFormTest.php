<?php

namespace Tests\Feature\Forms;

use PHPUnit\Framework\Attributes\DataProvider;
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
    #[DataProvider('tabProvider')]
    public function it_stays_on_the_same_tab_when_switching_forms($tab, $route)
    {
        $this->setTestRoles(['test' => ['access cp', 'configure forms', 'configure form connections']]);
        $user = tap(User::make()->assignRole('test'))->save();
        $form = tap(Form::make('test'))->save();
        FormSubmission::make()->form($form)->data(['foo' => 'bar'])->save();

        $this
            ->actingAs($user)
            ->from(cp_route('forms.index')."/other/{$tab}")
            ->get(cp_route('forms.show', $form->handle()))
            ->assertRedirect(cp_route($route, $form->handle()));
    }

    public static function tabProvider()
    {
        return [
            'builder' => ['builder', 'forms.builder.edit'],
            'logic' => ['logic', 'forms.logic.edit'],
            'connect' => ['connect', 'forms.connect.index'],
            'connector' => ['connect/email', 'forms.connect.index'],
            'submissions' => ['submissions', 'forms.submissions.index'],
            'submission' => ['submissions/123', 'forms.submissions.index'],
            'configure' => ['edit', 'forms.edit'],
        ];
    }

    #[Test]
    public function it_stays_on_submissions_when_switching_to_a_form_without_submissions()
    {
        $this->setTestRoles(['test' => ['access cp', 'configure forms']]);
        $user = tap(User::make()->assignRole('test'))->save();
        $form = tap(Form::make('test'))->save();

        $this
            ->actingAs($user)
            ->from(cp_route('forms.index').'/other/submissions')
            ->get(cp_route('forms.show', $form->handle()))
            ->assertRedirect(cp_route('forms.submissions.index', $form->handle()));
    }

    #[Test]
    public function it_ignores_the_previous_tab_without_permission_for_it()
    {
        $this->setTestRoles(['test' => ['access cp', 'view test form submissions']]);
        $user = tap(User::make()->assignRole('test'))->save();
        $form = tap(Form::make('test'))->save();

        $this
            ->actingAs($user)
            ->from(cp_route('forms.index').'/other/builder')
            ->get(cp_route('forms.show', $form->handle()))
            ->assertRedirect(cp_route('forms.submissions.index', $form->handle()));
    }

    #[Test]
    public function it_uses_the_default_tab_when_coming_from_outside_a_form()
    {
        $this->setTestRoles(['test' => ['access cp', 'configure forms']]);
        $user = tap(User::make()->assignRole('test'))->save();
        $form = tap(Form::make('test'))->save();

        $this
            ->actingAs($user)
            ->from(cp_route('forms.index'))
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

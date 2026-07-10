<?php

namespace Tests\Feature\Forms;

use PHPUnit\Framework\Attributes\Test;
use Statamic\Facades\Blueprint;
use Statamic\Facades\Form;
use Statamic\Facades\User;
use Tests\FakesRoles;
use Tests\PreventSavingStacheItemsToDisk;
use Tests\TestCase;

class DeleteFakeSubmissionsActionTest extends TestCase
{
    use FakesRoles;
    use PreventSavingStacheItemsToDisk;

    protected function resolveApplicationConfiguration($app)
    {
        parent::resolveApplicationConfiguration($app);

        $app['config']['statamic.forms.forms'] = $this->fakeStacheDirectory.'/forms';
    }

    #[Test]
    public function it_deletes_all_fake_submissions_for_a_form()
    {
        $form = $this->makeForm('contact');
        $user = $this->userWithConfigureFormsPermission();

        $this->makeSubmission($form, ['name' => 'Real', '_fake' => false]);
        $this->makeSubmission($form, ['name' => 'Fake 1', '_fake' => true]);
        $this->makeSubmission($form, ['name' => 'Fake 2', '_fake' => true]);

        $this->assertCount(3, $form->querySubmissions()->get());
        $this->assertCount(2, $form->querySubmissions()->where('_fake', true)->get());

        $this
            ->actingAs($user)
            ->post(cp_route('forms.submissions.actions.run', $form->handle()), [
                'action' => 'delete_fake_submissions',
                'selections' => ['_all_fake_submissions_'],
                'context' => ['form' => $form->handle()],
                'values' => [],
            ])
            ->assertOk()
            ->assertJson([
                'success' => true,
            ]);

        $this->assertCount(1, $form->querySubmissions()->get());
        $this->assertCount(0, $form->querySubmissions()->where('_fake', true)->get());
    }

    #[Test]
    public function it_does_not_delete_fake_submissions_without_delete_permission()
    {
        $form = $this->makeForm('contact');
        $user = $this->userWithViewOnlyPermission($form->handle());

        $this->makeSubmission($form, ['name' => 'Fake 1', '_fake' => true]);
        $this->makeSubmission($form, ['name' => 'Fake 2', '_fake' => true]);

        $this
            ->actingAs($user)
            ->post(cp_route('forms.submissions.actions.run', $form->handle()), [
                'action' => 'delete_fake_submissions',
                'selections' => ['_all_fake_submissions_'],
                'context' => ['form' => $form->handle()],
                'values' => [],
            ])
            ->assertOk()
            ->assertJson([
                'success' => false,
            ]);

        $this->assertCount(2, $form->querySubmissions()->where('_fake', true)->get());
    }

    #[Test]
    public function it_does_not_delete_fake_submissions_with_only_view_and_delete_submissions_permissions()
    {
        $form = $this->makeForm('contact');
        $user = $this->userWithSubmissionPermissions($form->handle());

        $this->makeSubmission($form, ['name' => 'Fake 1', '_fake' => true]);
        $this->makeSubmission($form, ['name' => 'Fake 2', '_fake' => true]);

        $this
            ->actingAs($user)
            ->post(cp_route('forms.submissions.actions.run', $form->handle()), [
                'action' => 'delete_fake_submissions',
                'selections' => ['_all_fake_submissions_'],
                'context' => ['form' => $form->handle()],
                'values' => [],
            ])
            ->assertOk()
            ->assertJson([
                'success' => false,
            ]);

        $this->assertCount(2, $form->querySubmissions()->where('_fake', true)->get());
    }

    private function makeForm(string $handle)
    {
        Blueprint::makeFromFields([
            'name' => ['type' => 'text'],
        ])->setHandle($handle)->setNamespace('forms')->save();

        return tap(Form::make($handle)->title('Contact'))->save();
    }

    private function makeSubmission($form, array $values)
    {
        $submission = $form->makeSubmission();
        $submission->data(collect($values));
        $submission->save();

        return $submission;
    }

    private function userWithSubmissionPermissions(string $formHandle)
    {
        $this->setTestRoles([
            'test' => [
                'access cp',
                "view {$formHandle} form submissions",
                "delete {$formHandle} form submissions",
            ],
        ]);

        return User::make()->assignRole('test')->save();
    }

    private function userWithViewOnlyPermission(string $formHandle)
    {
        $this->setTestRoles([
            'test' => [
                'access cp',
                "view {$formHandle} form submissions",
            ],
        ]);

        return User::make()->assignRole('test')->save();
    }

    private function userWithConfigureFormsPermission()
    {
        $this->setTestRoles(['test' => ['access cp', 'configure forms']]);

        return User::make()->assignRole('test')->save();
    }
}

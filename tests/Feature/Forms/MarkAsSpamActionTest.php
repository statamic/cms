<?php

namespace Tests\Feature\Forms;

use PHPUnit\Framework\Attributes\Test;
use Statamic\Facades\Blueprint;
use Statamic\Facades\Form;
use Statamic\Facades\User;
use Tests\FakesRoles;
use Tests\PreventSavingStacheItemsToDisk;
use Tests\TestCase;

class MarkAsSpamActionTest extends TestCase
{
    use FakesRoles;
    use PreventSavingStacheItemsToDisk;

    protected function resolveApplicationConfiguration($app)
    {
        parent::resolveApplicationConfiguration($app);

        $app['config']['statamic.forms.forms'] = $this->fakeStacheDirectory.'/forms';
    }

    #[Test]
    public function it_marks_a_finalized_submission_as_spam()
    {
        [$form, $submission] = $this->makeFormAndSubmission();

        $this->runAction($form, $submission)->assertOk()->assertJson(['success' => true]);

        $this->assertTrue($form->submission($submission->id())->isSpam());
    }

    #[Test]
    public function it_cannot_mark_a_partial_submission_as_spam()
    {
        [$form, $submission] = $this->makeFormAndSubmission(partial: true);

        $this->runAction($form, $submission)->assertForbidden();

        $this->assertFalse($form->submission($submission->id())->isSpam());
    }

    private function runAction($form, $submission)
    {
        return $this
            ->actingAs(tap(User::make()->makeSuper())->save())
            ->postJson(cp_route('forms.submissions.actions.run', $form->handle()), [
                'action' => 'mark_as_spam',
                'selections' => [$submission->id()],
                'context' => ['form' => $form->handle()],
                'values' => [],
            ]);
    }

    private function makeFormAndSubmission(bool $partial = false)
    {
        Blueprint::makeFromFields(['name' => ['type' => 'text']])->setHandle('contact')->setNamespace('forms')->save();

        $form = tap(Form::make('contact')->title('Contact'))->save();

        $submission = $form->makeSubmission()->data(['name' => 'Olaf']);
        $partial ? $submission->asPartial() : null;
        $submission->save();

        return [$form, $submission];
    }
}

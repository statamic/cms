<?php

namespace Tests\Feature\Forms;

use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Event;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Events\SubmissionFinalized;
use Statamic\Facades\Blueprint;
use Statamic\Facades\Form;
use Statamic\Facades\User;
use Statamic\Forms\CreateAssetsFromFileUploads;
use Statamic\Forms\SendEmail;
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

    #[Test]
    public function it_cannot_mark_a_non_spam_partial_as_not_spam()
    {
        Bus::fake();
        Event::fake([SubmissionFinalized::class]);
        [$form, $submission] = $this->makeFormAndSubmission(partial: true);

        $this->runAction($form, $submission, 'mark_as_not_spam')->assertForbidden();

        $this->assertTrue($form->submission($submission->id())->isPartial());
        Event::assertNotDispatched(SubmissionFinalized::class);
        Bus::assertNotDispatched(CreateAssetsFromFileUploads::class);
        Bus::assertNotDispatched(SendEmail::class);
    }

    #[Test]
    public function it_cannot_mark_a_finalized_non_spam_submission_as_not_spam()
    {
        [$form, $submission] = $this->makeFormAndSubmission();

        $this->runAction($form, $submission, 'mark_as_not_spam')->assertForbidden();
    }

    #[Test]
    public function it_cannot_mark_a_mixed_selection_as_not_spam()
    {
        Bus::fake();
        Event::fake([SubmissionFinalized::class]);
        [$form, $partial] = $this->makeFormAndSubmission(partial: true);
        $spam = tap($form->makeSubmission()->asPartial()->markAsSpam()->data(['name' => 'Bot']))->save();

        $this->runAction($form, $partial, 'mark_as_not_spam', [$spam->id(), $partial->id()])->assertForbidden();

        $this->assertTrue($form->submission($spam->id())->isSpam());
        $this->assertTrue($form->submission($partial->id())->isPartial());
        Event::assertNotDispatched(SubmissionFinalized::class);
        Bus::assertNotDispatched(CreateAssetsFromFileUploads::class);
        Bus::assertNotDispatched(SendEmail::class);
    }

    private function runAction($form, $submission, string $action = 'mark_as_spam', ?array $selections = null)
    {
        return $this
            ->actingAs(tap(User::make()->makeSuper())->save())
            ->postJson(cp_route('forms.submissions.actions.run', $form->handle()), [
                'action' => $action,
                'selections' => $selections ?? [$submission->id()],
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

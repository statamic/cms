<?php

namespace Tests\Actions;

use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Event;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Actions\MarkNotSpam;
use Statamic\Events\SubmissionCreated;
use Statamic\Facades\Form;
use Statamic\Forms\SendEmails;
use Tests\PreventSavingStacheItemsToDisk;
use Tests\TestCase;

class MarkNotSpamTest extends TestCase
{
    use PreventSavingStacheItemsToDisk;

    #[Test]
    public function it_is_only_visible_to_spam_submissions()
    {
        $this->markTestSkipped();

        $form = tap(Form::make('contact'))->save();

        $action = new MarkNotSpam;

        $this->assertFalse($action->visibleTo($form->makeSubmission()));
        $this->assertFalse($action->visibleTo($form->makeSubmission()->set('incomplete', true)));
        $this->assertTrue($action->visibleTo($form->makeSubmission()->set('spam', true)));
    }

    #[Test]
    public function it_removes_the_spam_key_and_dispatches_relevant_events()
    {
        $this->markTestSkipped();

        Bus::fake();
        Event::fake([SubmissionCreated::class]);

        $form = tap(Form::make('contact'))->save();
        $submission = $form->makeSubmission()->set('spam', true);
        $submission->save();

        (new MarkNotSpam)->run(collect([$submission]), []);

        $this->assertFalse($submission->isSpam());

        Event::assertDispatched(SubmissionCreated::class);
        Bus::assertDispatched(SendEmails::class);
    }
}

<?php

namespace Tests\Actions;

use Exception;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Event;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Actions\MarkAsNotSpam;
use Statamic\Events\SubmissionCreated;
use Statamic\Events\SubmissionCreating;
use Statamic\Events\SubmissionDeleted;
use Statamic\Events\SubmissionFinalized;
use Statamic\Events\SubmissionSaved;
use Statamic\Events\SubmissionSaving;
use Statamic\Facades\Form;
use Statamic\Facades\User;
use Statamic\Forms\CreateAssetsFromFileUploads;
use Statamic\Forms\DeleteTemporaryFiles;
use Statamic\Forms\SendEmail;
use Tests\FakesRoles;
use Tests\PreventSavingStacheItemsToDisk;
use Tests\TestCase;

class MarkAsNotSpamTest extends TestCase
{
    use FakesRoles;
    use PreventSavingStacheItemsToDisk;

    private $form;

    public function setUp(): void
    {
        parent::setUp();

        $this->form = tap(Form::make('contact'))->save();
    }

    public function tearDown(): void
    {
        $this->form->querySubmissions()->get()->each->delete();

        parent::tearDown();
    }

    #[Test]
    public function it_finalizes_submissions_that_were_caught_before_being_finalized()
    {
        Bus::fake();
        Event::fake([SubmissionFinalized::class]);

        $this->form->connections(['email' => [['to' => 'test@example.com']]])->save();

        $submission = tap($this->form->makeSubmission()->asPartial()->markAsSpam()->data(['name' => 'Olaf']))->save();

        (new MarkAsNotSpam)->run(collect([$submission]), []);

        $submission = $this->form->submission($submission->id());

        $this->assertFalse($submission->isSpam());
        $this->assertFalse($submission->isPartial());
        $this->assertEquals('finalized', $submission->status());

        Event::assertDispatched(SubmissionFinalized::class);
        Bus::assertDispatched(CreateAssetsFromFileUploads::class);
        Bus::assertDispatched(SendEmail::class);
    }

    #[Test]
    public function it_does_not_refinalize_submissions_that_were_flagged_after_being_finalized()
    {
        Bus::fake();
        Event::fake([SubmissionFinalized::class]);

        $this->form->connections(['email' => [['to' => 'test@example.com']]])->save();

        $submission = tap($this->form->makeSubmission()->markAsSpam()->data(['name' => 'Olaf']))->save();

        (new MarkAsNotSpam)->run(collect([$submission]), []);

        $submission = $this->form->submission($submission->id());

        $this->assertFalse($submission->isSpam());
        $this->assertEquals('finalized', $submission->status());

        Event::assertNotDispatched(SubmissionFinalized::class);
        Bus::assertNotDispatched(SendEmail::class);
    }

    #[Test]
    public function vetoing_the_creating_event_leaves_the_submission_as_spam_and_reports_a_failure()
    {
        Bus::fake();

        $this->form->connections(['email' => [['to' => 'test@example.com']]])->save();

        $submission = tap($this->form->makeSubmission()->asPartial()->markAsSpam()->data(['name' => 'Olaf']))->save();
        $stored = $this->form->submission($submission->id())->fileData();

        $events = [];
        foreach ([SubmissionCreating::class, SubmissionSaving::class, SubmissionCreated::class, SubmissionSaved::class, SubmissionFinalized::class, SubmissionDeleted::class] as $event) {
            Event::listen($event, function () use (&$events, $event) {
                $events[] = class_basename($event);
            });
        }
        Event::listen(SubmissionCreating::class, fn () => false);

        $message = null;

        try {
            (new MarkAsNotSpam)->run(collect([$submission]), []);
        } catch (Exception $e) {
            $message = $e->getMessage();
        }

        $this->assertEquals('Submission could not be marked as not spam.', $message);

        $this->assertEquals(['SubmissionCreating'], $events);
        $this->assertEquals($stored, $this->form->submission($submission->id())->fileData());
        $this->assertTrue($submission->isSpam());
        $this->assertEquals($stored, $submission->fileData());
        Bus::assertNotDispatched(SendEmail::class);
        Bus::assertNotDispatched(DeleteTemporaryFiles::class);
    }

    #[Test]
    public function it_reports_how_many_submissions_were_marked_as_not_spam_when_some_are_vetoed()
    {
        Bus::fake();

        $vetoed = tap($this->form->makeSubmission()->asPartial()->markAsSpam()->data(['name' => 'Olaf']))->save();
        $allowed = tap($this->form->makeSubmission()->asPartial()->markAsSpam()->data(['name' => 'Anna']))->save();

        Event::listen(SubmissionCreating::class, fn ($event) => $event->submission->id() === $vetoed->id() ? false : null);

        $message = null;

        try {
            (new MarkAsNotSpam)->run(collect([$vetoed, $allowed]), []);
        } catch (Exception $e) {
            $message = $e->getMessage();
        }

        $this->assertEquals('1/2 submissions were marked as not spam.', $message);

        $this->assertTrue($this->form->submission($vetoed->id())->isSpam());
        $this->assertEquals('finalized', $this->form->submission($allowed->id())->status());
    }

    #[Test]
    public function it_requires_permission_to_mark_submissions_as_spam()
    {
        $this->setTestRoles([
            'global' => ['view form submissions', 'manage form spam'],
            'form' => ['view contact form submissions', 'manage contact form spam'],
            'other_form' => ['view contact form submissions', 'manage other form spam'],
            'view_only' => ['view form submissions', 'view contact form submissions'],
            'noaccess' => [],
        ]);

        $submission = tap($this->form->makeSubmission()->markAsSpam()->data(['name' => 'Olaf']))->save();

        foreach (['global', 'form'] as $role) {
            $user = tap(User::make()->assignRole($role))->save();
            $this->assertTrue((new MarkAsNotSpam)->authorize($user, $submission), $role);
        }

        foreach (['other_form', 'view_only', 'noaccess'] as $role) {
            $user = tap(User::make()->assignRole($role))->save();
            $this->assertFalse((new MarkAsNotSpam)->authorize($user, $submission), $role);
        }
    }

    #[Test]
    public function it_only_authorizes_spam_submissions()
    {
        $this->setTestRoles(['global' => ['view form submissions', 'manage form spam']]);

        $super = tap(User::make()->makeSuper())->save();
        $user = tap(User::make()->assignRole('global'))->save();
        $spam = $this->form->makeSubmission()->markAsSpam();
        $submission = $this->form->makeSubmission();
        $partial = $this->form->makeSubmission()->asPartial();

        foreach ([$super, $user] as $u) {
            $this->assertTrue((new MarkAsNotSpam)->authorize($u, $spam));
            $this->assertFalse((new MarkAsNotSpam)->authorize($u, $submission));
            $this->assertFalse((new MarkAsNotSpam)->authorize($u, $partial));
        }
    }

    #[Test]
    public function it_is_only_visible_to_spam_submissions()
    {
        $submission = $this->form->makeSubmission();
        $spam = $this->form->makeSubmission()->markAsSpam();

        $this->assertTrue((new MarkAsNotSpam)->visibleTo($spam));
        $this->assertFalse((new MarkAsNotSpam)->visibleTo($submission));
        $this->assertFalse((new MarkAsNotSpam)->visibleTo($this->form));
    }
}

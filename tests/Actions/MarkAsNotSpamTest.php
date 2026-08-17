<?php

namespace Tests\Actions;

use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Event;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Actions\MarkAsNotSpam;
use Statamic\Events\SubmissionFinalized;
use Statamic\Facades\Form;
use Statamic\Facades\User;
use Statamic\Forms\CreateAssetsFromFileUploads;
use Statamic\Forms\SendEmails;
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
        $this->form->submissions()->each->delete();

        parent::tearDown();
    }

    #[Test]
    public function it_finalizes_submissions_that_were_caught_before_being_finalized()
    {
        Bus::fake();
        Event::fake([SubmissionFinalized::class]);

        $submission = tap($this->form->makeSubmission()->asPartial()->markAsSpam()->data(['name' => 'Olaf']))->save();

        (new MarkAsNotSpam)->run(collect([$submission]), []);

        $submission = $this->form->submission($submission->id());

        $this->assertFalse($submission->isSpam());
        $this->assertFalse($submission->isPartial());
        $this->assertEquals('finalized', $submission->status());

        Event::assertDispatched(SubmissionFinalized::class);
        Bus::assertDispatched(CreateAssetsFromFileUploads::class);
        Bus::assertDispatched(SendEmails::class);
    }

    #[Test]
    public function it_does_not_refinalize_submissions_that_were_flagged_after_being_finalized()
    {
        Bus::fake();
        Event::fake([SubmissionFinalized::class]);

        $submission = tap($this->form->makeSubmission()->markAsSpam()->data(['name' => 'Olaf']))->save();

        (new MarkAsNotSpam)->run(collect([$submission]), []);

        $submission = $this->form->submission($submission->id());

        $this->assertFalse($submission->isSpam());
        $this->assertEquals('finalized', $submission->status());

        Event::assertNotDispatched(SubmissionFinalized::class);
        Bus::assertNotDispatched(SendEmails::class);
    }

    #[Test]
    public function it_requires_permission_to_view_submissions()
    {
        $this->setTestRoles([
            'access' => ['view form submissions'],
            'noaccess' => [],
        ]);

        $userWithPermission = tap(User::make()->assignRole('access'))->save();
        $userWithoutPermission = tap(User::make()->assignRole('noaccess'))->save();
        $submission = tap($this->form->makeSubmission()->markAsSpam()->data(['name' => 'Olaf']))->save();

        $this->assertTrue((new MarkAsNotSpam)->authorize($userWithPermission, $submission));
        $this->assertFalse((new MarkAsNotSpam)->authorize($userWithoutPermission, $submission));
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

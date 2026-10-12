<?php

namespace Tests\Forms;

use Carbon\Carbon;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Support\Facades\Storage;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Facades\Form;
use Statamic\Jobs\DeleteSpamFormSubmissions;
use Tests\PreventSavingStacheItemsToDisk;
use Tests\TestCase;

class DeleteSpamFormSubmissionsTest extends TestCase
{
    use PreventSavingStacheItemsToDisk;

    protected function resolveApplicationConfiguration($app)
    {
        parent::resolveApplicationConfiguration($app);

        $app['config']['statamic.forms.forms'] = $this->fakeStacheDirectory.'/forms';
    }

    #[Test]
    public function it_deletes_spam_submissions_older_than_the_configured_threshold()
    {
        config(['statamic.forms.delete_spam_submissions_after' => 30]);

        $form = tap(Form::make('contact'))->save();

        Carbon::setTestNow('2025-05-01 12:00:00');
        $oldSpam = tap($form->makeSubmission()->markAsSpam())->save();

        Carbon::setTestNow('2025-06-10 12:00:00');
        $recentSpam = tap($form->makeSubmission()->markAsSpam())->save();

        Carbon::setTestNow('2025-06-15 12:00:00');

        (new DeleteSpamFormSubmissions)->handle();

        $this->assertNull($form->submission($oldSpam->id()));
        $this->assertNotNull($form->submission($recentSpam->id()));
    }

    #[Test]
    public function it_only_deletes_spam_submissions()
    {
        config(['statamic.forms.delete_spam_submissions_after' => 30]);

        $form = tap(Form::make('contact'))->save();

        Carbon::setTestNow('2025-05-01 12:00:00');
        $spam = tap($form->makeSubmission()->markAsSpam())->save();

        Carbon::setTestNow('2025-05-02 12:00:00');
        $finalized = tap($form->makeSubmission())->save();

        Carbon::setTestNow('2025-05-03 12:00:00');
        $partial = tap($form->makeSubmission()->asPartial())->save();

        Carbon::setTestNow('2025-07-15 12:00:00');

        (new DeleteSpamFormSubmissions)->handle();

        $this->assertNull($form->submission($spam->id()));
        $this->assertNotNull($form->submission($finalized->id()));
        $this->assertNotNull($form->submission($partial->id()));
    }

    #[Test]
    public function it_does_not_delete_anything_when_disabled()
    {
        config(['statamic.forms.delete_spam_submissions_after' => null]);

        $form = tap(Form::make('contact'))->save();

        Carbon::setTestNow('2025-05-01 12:00:00');
        $spam = tap($form->makeSubmission()->markAsSpam())->save();

        Carbon::setTestNow('2026-05-01 12:00:00');

        (new DeleteSpamFormSubmissions)->handle();

        $this->assertNotNull($form->submission($spam->id()));
    }

    #[Test]
    public function it_deletes_the_upload_directory_of_deleted_spam()
    {
        config(['statamic.forms.delete_spam_submissions_after' => 30]);

        $disk = Storage::fake('local');

        $form = tap(Form::make('contact'))->save();

        Carbon::setTestNow('2025-05-01 12:00:00');
        $spam = tap($form->makeSubmission()->markAsSpam())->save();
        $disk->put("statamic/form-uploads/{$spam->id()}/document/file.txt", 'spam');

        Carbon::setTestNow('2025-07-15 12:00:00');

        (new DeleteSpamFormSubmissions)->handle();

        $this->assertNull($form->submission($spam->id()));
        $disk->assertMissing("statamic/form-uploads/{$spam->id()}/document/file.txt");
    }

    #[Test]
    public function it_does_not_resave_the_submission_after_deleting_it()
    {
        config(['statamic.forms.delete_spam_submissions_after' => 30]);

        $form = tap(Form::make('contact'))->save();

        Carbon::setTestNow('2025-05-01 12:00:00');
        $spam = tap($form->makeSubmission()->markAsSpam())->save();

        Carbon::setTestNow('2025-07-15 12:00:00');

        $saved = 0;
        \Illuminate\Support\Facades\Event::listen(\Statamic\Events\SubmissionSaved::class, function () use (&$saved) {
            $saved++;
        });

        (new DeleteSpamFormSubmissions)->handle();

        $this->assertEquals(0, $saved);
        $this->assertNull($form->submission($spam->id()));
    }

    #[Test]
    public function it_keeps_uploads_of_spam_that_is_not_yet_old_enough()
    {
        config(['statamic.forms.delete_spam_submissions_after' => 30]);

        $disk = Storage::fake('local');

        $form = tap(Form::make('contact'))->save();

        Carbon::setTestNow('2025-06-10 12:00:00');
        $spam = tap($form->makeSubmission()->markAsSpam())->save();
        $disk->put("statamic/form-uploads/{$spam->id()}/document/file.txt", 'spam');

        Carbon::setTestNow('2025-06-15 12:00:00');

        (new DeleteSpamFormSubmissions)->handle();

        $disk->assertExists("statamic/form-uploads/{$spam->id()}/document/file.txt");
    }

    #[Test]
    public function it_is_scheduled_daily()
    {
        $event = collect(app(Schedule::class)->events())
            ->first(fn ($event) => str_contains($event->description ?? '', DeleteSpamFormSubmissions::class));

        $this->assertNotNull($event);
        $this->assertEquals('0 0 * * *', $event->expression);
    }
}

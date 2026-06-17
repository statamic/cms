<?php

namespace Tests\Forms;

use Carbon\Carbon;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Facades\Form;
use Statamic\Jobs\DeleteIncompleteFormSubmissions;
use Tests\PreventSavingStacheItemsToDisk;
use Tests\TestCase;

class DeleteIncompleteFormSubmissionsTest extends TestCase
{
    use PreventSavingStacheItemsToDisk;

    #[Test]
    public function it_deletes_drafts_older_than_the_configured_threshold()
    {
        config(['statamic.forms.delete_incomplete_submissions_after' => 7]);

        $form = tap(Form::make('contact'))->save();

        Carbon::setTestNow('2025-06-01 12:00:00');
        $oldDraft = tap($form->makeSubmission()->set('draft', true))->save();

        Carbon::setTestNow('2025-06-02 12:00:00');
        $oldSubmission = tap($form->makeSubmission())->save();

        Carbon::setTestNow('2025-06-14 12:00:00');
        $recentDraft = tap($form->makeSubmission()->set('draft', true))->save();

        Carbon::setTestNow('2025-06-15 12:00:00');

        (new DeleteIncompleteFormSubmissions)->handle();

        $this->assertNull($form->submission($oldDraft->id()));
        $this->assertNotNull($form->submission($recentDraft->id()));
        $this->assertNotNull($form->submission($oldSubmission->id()));
    }

    #[Test]
    public function it_only_deletes_spam_submissions()
    {
        config(['statamic.forms.delete_incomplete_submissions_after' => 7]);

        $form = tap(Form::make('contact'))->save();

        Carbon::setTestNow('2025-06-01 12:00:00');
        $draft = tap($form->makeSubmission()->set('draft', true))->save();

        Carbon::setTestNow('2025-06-02 12:00:00');
        $submitted = tap($form->makeSubmission())->save();

        Carbon::setTestNow('2025-06-03 12:00:00');
        $spam = tap($form->makeSubmission()->set('spam', true))->save();

        Carbon::setTestNow('2025-06-30 12:00:00');

        (new DeleteIncompleteFormSubmissions)->handle();

        $this->assertNull($form->submission($draft->id()));
        $this->assertNotNull($form->submission($submitted->id()));
        $this->assertNotNull($form->submission($spam->id()));
    }

    #[Test]
    public function it_does_not_delete_anything_when_disabled()
    {
        config(['statamic.forms.delete_incomplete_submissions_after' => null]);

        $form = tap(Form::make('contact'))->save();

        Carbon::setTestNow('2025-06-01 12:00:00');
        $draft = tap($form->makeSubmission()->set('draft', true))->save();

        Carbon::setTestNow('2025-06-30 12:00:00');

        (new DeleteIncompleteFormSubmissions)->handle();

        $this->assertNotNull($form->submission($draft->id()));
    }
}

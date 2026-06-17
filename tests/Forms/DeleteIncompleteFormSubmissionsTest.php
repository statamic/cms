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

    protected function resolveApplicationConfiguration($app)
    {
        parent::resolveApplicationConfiguration($app);

        $app['config']['statamic.forms.forms'] = $this->fakeStacheDirectory.'/forms';
    }

    #[Test]
    public function it_deletes_incomplete_submissions_older_than_the_configured_threshold()
    {
        config(['statamic.forms.delete_incomplete_submissions_after' => 7]);

        $form = tap(Form::make('contact'))->save();

        Carbon::setTestNow('2025-06-01 12:00:00');
        $oldIncomplete = tap($form->makeSubmission()->set('incomplete', true))->save();

        Carbon::setTestNow('2025-06-02 12:00:00');
        $oldComplete = tap($form->makeSubmission())->save();

        Carbon::setTestNow('2025-06-14 12:00:00');
        $recentIncomplete = tap($form->makeSubmission()->set('incomplete', true))->save();

        Carbon::setTestNow('2025-06-15 12:00:00');

        (new DeleteIncompleteFormSubmissions)->handle();

        $this->assertNull($form->submission($oldIncomplete->id()));
        $this->assertNotNull($form->submission($recentIncomplete->id()));
        $this->assertNotNull($form->submission($oldComplete->id()));
    }

    #[Test]
    public function it_only_deletes_incomplete_submissions_never_complete_or_spam()
    {
        config(['statamic.forms.delete_incomplete_submissions_after' => 7]);

        $form = tap(Form::make('contact'))->save();

        Carbon::setTestNow('2025-06-01 12:00:00');
        $incomplete = tap($form->makeSubmission()->set('incomplete', true))->save();

        Carbon::setTestNow('2025-06-02 12:00:00');
        $complete = tap($form->makeSubmission())->save();

        Carbon::setTestNow('2025-06-03 12:00:00');
        $spam = tap($form->makeSubmission()->set('spam', true))->save();

        Carbon::setTestNow('2025-06-30 12:00:00');

        (new DeleteIncompleteFormSubmissions)->handle();

        $this->assertNull($form->submission($incomplete->id()));
        $this->assertNotNull($form->submission($complete->id()));
        $this->assertNotNull($form->submission($spam->id()));
    }

    #[Test]
    public function it_does_not_delete_anything_when_disabled()
    {
        config(['statamic.forms.delete_incomplete_submissions_after' => null]);

        $form = tap(Form::make('contact'))->save();

        Carbon::setTestNow('2025-06-01 12:00:00');
        $incomplete = tap($form->makeSubmission()->set('incomplete', true))->save();

        Carbon::setTestNow('2025-06-30 12:00:00');

        (new DeleteIncompleteFormSubmissions)->handle();

        $this->assertNotNull($form->submission($incomplete->id()));
    }
}

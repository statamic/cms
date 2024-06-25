<?php

namespace Tests\Forms;

use Illuminate\Support\Facades\Bus;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Facades\Form as FacadesForm;
use Statamic\Facades\Site;
use Statamic\Forms\DeleteTemporaryAttachments;
use Statamic\Forms\SendEmail;
use Statamic\Forms\SendEmails;
use Tests\PreventSavingStacheItemsToDisk;
use Tests\TestCase;

class SendEmailsTest extends TestCase
{
    use PreventSavingStacheItemsToDisk;

    #[Test]
    public function it_queues_email_jobs()
    {
        Bus::fake();

        $form = tap(FacadesForm::make('test')->email([
            [
                'from' => 'first@sender.com',
                'to' => 'first@recipient.com',
                'foo' => 'bar',
                'unparsed' => '{{ test }}',
            ], [
                'from' => 'second@sender.com',
                'to' => 'second@recipient.com',
                'baz' => 'qux',
            ],
        ]))->save();

        (new SendEmails(
            $submission = $form->makeSubmission(),
            $site = Site::default(),
        ))->handle();

        Bus::assertChained([
            new SendEmail($submission, $site, [
                'from' => 'first@sender.com',
                'to' => 'first@recipient.com',
                'foo' => 'bar',
                // test that the config is passed along unparsed.
                // the email class will handle that. we don't want to double parse.
                'unparsed' => '{{ test }}',
            ]),
            new SendEmail($submission, $site, [
                'from' => 'second@sender.com',
                'to' => 'second@recipient.com',
                'baz' => 'qux',
            ]),
        ]);
    }

    #[Test]
    public function it_queues_email_jobs_when_config_contains_single_email()
    {
        // The email config should be an array of email configs.
        // e.g. [ [to,from,...], [to,from,...], ... ]
        // but it's possible that a user may have only one email config.
        // e.g. [to,from,...]

        Bus::fake();

        $form = tap(FacadesForm::make('test')->email([
            'from' => 'first@sender.com',
            'to' => 'first@recipient.com',
            'foo' => 'bar',
        ]))->save();

        (new SendEmails(
            $submission = $form->makeSubmission(),
            $site = Site::default(),
        ))->handle();

        Bus::assertChained([
            new SendEmail($submission, $site, [
                'from' => 'first@sender.com',
                'to' => 'first@recipient.com',
                'foo' => 'bar',
            ]),
        ]);
    }

    #[Test]
    public function it_dispatches_delete_attachments_job_after_dispatching_email_jobs()
    {
        Bus::fake();

        $form = tap(FacadesForm::make('attachments_test')->email([
            'from' => 'first@sender.com',
            'to' => 'first@recipient.com',
            'foo' => 'bar',
        ]))->save();

        $form->blueprint()->ensureField('attachments', ['type' => 'files'])->save();

        (new SendEmails(
            $submission = $form->makeSubmission(),
            $site = Site::default(),
        ))->handle();

        Bus::assertChained([
            new SendEmail($submission, $site, [
                'from' => 'first@sender.com',
                'to' => 'first@recipient.com',
                'foo' => 'bar',
            ]),
            new DeleteTemporaryAttachments($submission),
        ]);
    }

    #[Test]
    #[DataProvider('noEmailsProvider')]
    public function no_email_jobs_are_queued_if_none_are_configured($emailConfig)
    {
        Bus::fake();

        $form = tap(FacadesForm::make('test')->email($emailConfig))->save();

        (new SendEmails(
            $form->makeSubmission(),
            Site::default(),
        ))->handle();

        Bus::assertNothingDispatched();
    }

    public static function noEmailsProvider()
    {
        return [
            'null' => [null],
            'empty array' => [[]],
        ];
    }
}

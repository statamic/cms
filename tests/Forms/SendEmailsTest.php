<?php

namespace Tests\Forms;

use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Storage;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Facades\Form as FacadesForm;
use Statamic\Facades\Site;
use Statamic\Forms\DeleteTemporaryFiles;
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
    public function it_dispatches_delete_temporary_files_job_after_dispatching_email_jobs()
    {
        Bus::fake();

        $form = tap(FacadesForm::make('attachments_test')->email([
            'from' => 'first@sender.com',
            'to' => 'first@recipient.com',
            'foo' => 'bar',
        ])->formFields([
            'fields' => [
                ['handle' => 'document', 'field' => ['type' => 'form_upload', 'store' => false]],
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
            ]),
            new DeleteTemporaryFiles($submission),
        ]);
    }

    #[Test]
    public function it_dispatches_delete_temporary_files_job_even_without_any_emails_configured()
    {
        Bus::fake();

        $form = tap(FacadesForm::make('attachments_test')->formFields([
            'fields' => [
                ['handle' => 'document', 'field' => ['type' => 'form_upload', 'store' => false]],
            ],
        ]))->save();

        (new SendEmails(
            $form->makeSubmission(),
            Site::default(),
        ))->handle();

        Bus::assertDispatched(DeleteTemporaryFiles::class);
    }

    #[Test]
    public function delete_attachments_job_deletes_files_from_the_configured_disk_and_path()
    {
        config([
            'statamic.system.file_uploads_disk' => 'uploads',
            'statamic.system.file_uploads_path' => 'temp-uploads',
        ]);

        $localDisk = Storage::fake('local');
        $uploadsDisk = Storage::fake('uploads');
        $uploadsDisk->put('temp-uploads/1234567/file.txt', 'contents');
        $localDisk->put('statamic/file-uploads/1234567/file.txt', 'contents');

        $form = tap(FacadesForm::make('attachments_test')->email([
            'from' => 'first@sender.com',
            'to' => 'first@recipient.com',
        ]))->save();

        $form->blueprint()->ensureField('attachments', ['type' => 'files'])->save();

        $submission = $form->makeSubmission()->data(['attachments' => ['1234567/file.txt']]);

        (new DeleteTemporaryFiles($submission))->handle();

        $uploadsDisk->assertMissing('temp-uploads/1234567/file.txt');
        $localDisk->assertExists('statamic/file-uploads/1234567/file.txt');
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

    #[Test]
    public function it_skips_disabled_email_configs()
    {
        Bus::fake();

        $form = tap(FacadesForm::make('test')->connections(['email' => [
            [
                'from' => 'first@sender.com',
                'to' => 'first@recipient.com',
                'enabled' => false,
            ], [
                'from' => 'second@sender.com',
                'to' => 'second@recipient.com',
                'enabled' => true,
            ], [
                'from' => 'third@sender.com',
                'to' => 'third@recipient.com',
            ],
        ]]))->save();

        (new SendEmails(
            $submission = $form->makeSubmission(),
            $site = Site::default(),
        ))->handle();

        Bus::assertChained([
            new SendEmail($submission, $site, [
                'from' => 'second@sender.com',
                'to' => 'second@recipient.com',
                'enabled' => true,
            ]),
            new SendEmail($submission, $site, [
                'from' => 'third@sender.com',
                'to' => 'third@recipient.com',
            ]),
        ]);
    }

    #[Test]
    #[DataProvider('emailConditionsProvider')]
    public function it_filters_email_configs_using_conditions($conditions, $value, $shouldSend)
    {
        Bus::fake();

        $config = [
            'from' => 'first@sender.com',
            'to' => 'first@recipient.com',
            'conditions' => $conditions,
        ];

        $form = tap(FacadesForm::make('test')->formFields([
            'fields' => [
                ['handle' => 'how_did_you_hear', 'field' => ['type' => 'text']],
            ],
        ])->connections(['email' => [$config]]))->save();

        (new SendEmails(
            $submission = $form->makeSubmission()->data(['how_did_you_hear' => $value]),
            $site = Site::default(),
        ))->handle();

        $shouldSend
            ? Bus::assertChained([new SendEmail($submission, $site, $config)])
            : Bus::assertNothingDispatched();
    }

    public static function emailConditionsProvider()
    {
        $conditions = [['field' => 'how_did_you_hear', 'operator' => 'equals', 'value' => 'friend']];

        return [
            'no conditions' => [[], 'google', true],
            'matching conditions' => [$conditions, 'friend', true],
            'non-matching conditions' => [$conditions, 'google', false],
        ];
    }
}

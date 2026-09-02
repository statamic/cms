<?php

namespace Tests\Forms;

use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Contracts\Forms\Submission;
use Statamic\Facades\Form as FacadesForm;
use Statamic\Facades\Site;
use Statamic\Facades\Stache;
use Statamic\Forms\Email;
use Statamic\Forms\SendEmails;
use Statamic\Support\Arr;
use Tests\PreventSavingStacheItemsToDisk;
use Tests\TestCase;

class SendEmailsTest extends TestCase
{
    use PreventSavingStacheItemsToDisk;

    #[Test]
    public function it_sends_an_email_per_config()
    {
        Mail::fake();

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

        $this->sendEmails($form->makeSubmission());

        Mail::assertSent(Email::class, 2);
        Mail::assertSent(Email::class, fn (Email $email) => Arr::except($email->getConfig(), 'id') === [
            'from' => 'first@sender.com',
            'to' => 'first@recipient.com',
            'foo' => 'bar',
            // test that the config is passed along unparsed.
            // the email class will handle that. we don't want to double parse.
            'unparsed' => '{{ test }}',
        ]);
        Mail::assertSent(Email::class, fn (Email $email) => Arr::except($email->getConfig(), 'id') === [
            'from' => 'second@sender.com',
            'to' => 'second@recipient.com',
            'baz' => 'qux',
        ]);
    }

    #[Test]
    public function it_sends_emails_in_the_order_they_are_configured()
    {
        Mail::fake();

        $form = tap(FacadesForm::make('test')->email([
            ['to' => 'first@recipient.com'],
            ['to' => 'second@recipient.com'],
            ['to' => 'third@recipient.com'],
        ]))->save();

        $this->sendEmails($form->makeSubmission());

        $this->assertEquals(
            ['first@recipient.com', 'second@recipient.com', 'third@recipient.com'],
            Mail::sent(Email::class)->map(fn (Email $email) => $email->getConfig()['to'])->all()
        );
    }

    #[Test]
    public function it_sends_an_email_when_config_contains_single_email()
    {
        // The email config should be an array of email configs.
        // e.g. [ [to,from,...], [to,from,...], ... ]
        // but it's possible that a user may have only one email config.
        // e.g. [to,from,...]

        Mail::fake();

        $form = tap(FacadesForm::make('test')->email([
            'from' => 'first@sender.com',
            'to' => 'first@recipient.com',
            'foo' => 'bar',
        ]))->save();

        $this->sendEmails($form->makeSubmission());

        Mail::assertSent(Email::class, 1);
        Mail::assertSent(Email::class, fn (Email $email) => Arr::except($email->getConfig(), 'id') === [
            'from' => 'first@sender.com',
            'to' => 'first@recipient.com',
            'foo' => 'bar',
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

        $form->blueprint()->ensureField('attachments', ['type' => 'files']);

        $submission = $form->makeSubmission()->data(['attachments' => ['1234567/file.txt']]);

        (new DeleteTemporaryFiles($submission))->handle();

        $uploadsDisk->assertMissing('temp-uploads/1234567/file.txt');
        $localDisk->assertExists('statamic/file-uploads/1234567/file.txt');
    }

    #[Test]
    #[DataProvider('noEmailsProvider')]
    public function no_emails_are_sent_if_none_are_configured($emailConfig)
    {
        Mail::fake();

        $form = tap(FacadesForm::make('test')->email($emailConfig))->save();

        $this->sendEmails($form->makeSubmission());

        Mail::assertNothingSent();
    }

    public static function noEmailsProvider()
    {
        return [
            'null' => [null],
            'empty array' => [[]],
        ];
    }

    #[Test]
    #[DataProvider('recipientFieldProvider')]
    public function it_skips_emails_whose_recipients_dont_resolve($value, $shouldSend)
    {
        Mail::fake();

        $form = tap(FacadesForm::make('test')->formFields([
            'fields' => [
                ['handle' => 'email', 'field' => ['type' => 'text']],
            ],
        ])->connections(['email' => [
            ['id' => 'one', 'to' => ['field:email']],
        ]]))->save();

        $this->sendEmails($form->makeSubmission()->data(['email' => $value]));

        $shouldSend
            ? Mail::assertSent(Email::class, 1)
            : Mail::assertNothingSent();
    }

    public static function recipientFieldProvider()
    {
        return [
            'field was filled in' => ['someone@example.com', true],
            'field was left blank' => [null, false],
            'field contains junk' => ['not an email', false],
        ];
    }

    #[Test]
    public function it_skips_disabled_email_configs()
    {
        Mail::fake();

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

        $this->sendEmails($form->makeSubmission());

        Mail::assertSent(Email::class, 2);
        Mail::assertNotSent(Email::class, fn (Email $email) => $email->getConfig()['from'] === 'first@sender.com');
    }

    #[Test]
    #[DataProvider('emailConditionsProvider')]
    public function it_filters_email_configs_using_conditions($conditions, $value, $shouldSend)
    {
        Mail::fake();

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

        $this->sendEmails($form->makeSubmission()->data(['how_did_you_hear' => $value]));

        $shouldSend
            ? Mail::assertSent(Email::class, 1)
            : Mail::assertNothingSent();
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

    #[Test]
    public function it_uses_the_configured_configs_instead_of_the_forms_own_when_set()
    {
        Mail::fake();

        $form = tap(FacadesForm::make('test')->email([
            ['from' => 'form@sender.com', 'to' => 'form@recipient.com'],
        ]))->save();

        $submission = $form->makeSubmission();

        Bus::chain([
            new SendEmails($submission, Site::default(), [
                ['id' => 'override', 'from' => 'override@sender.com', 'to' => 'override@recipient.com'],
            ]),
        ])->dispatch();

        Mail::assertSent(Email::class, 1);
        Mail::assertSent(Email::class, fn (Email $email) => $email->getConfig()['to'] === 'override@recipient.com');
    }

    #[Test]
    public function it_reads_connections_from_the_dispatched_submissions_form_instance()
    {
        Mail::fake();

        $form = tap(FacadesForm::make('test')->email([
            ['from' => 'saved@sender.com', 'to' => 'saved@recipient.com'],
        ]))->save();

        $submission = tap($form->makeSubmission())->save();

        Stache::clear();

        $form->email([
            ['from' => 'changed@sender.com', 'to' => 'changed@recipient.com'],
        ]);

        $this->sendEmails($submission);

        Mail::assertSent(Email::class, 1);
        Mail::assertSent(Email::class, fn (Email $email) => $email->getConfig()['to'] === 'changed@recipient.com');
    }

    #[Test]
    public function it_uses_the_configured_configs_even_when_the_forms_connections_were_changed_in_memory()
    {
        Mail::fake();

        $form = tap(FacadesForm::make('test')->email([
            ['from' => 'saved@sender.com', 'to' => 'saved@recipient.com'],
        ]))->save();

        $submission = tap($form->makeSubmission())->save();

        $form->email([
            ['from' => 'changed@sender.com', 'to' => 'changed@recipient.com'],
        ]);

        Bus::chain([
            new SendEmails($submission, Site::default(), [
                ['id' => 'override', 'from' => 'override@sender.com', 'to' => 'override@recipient.com'],
            ]),
        ])->dispatch();

        Mail::assertSent(Email::class, 1);
        Mail::assertSent(Email::class, fn (Email $email) => $email->getConfig()['to'] === 'override@recipient.com');
    }

    private function sendEmails(Submission $submission): void
    {
        Bus::chain([new SendEmails($submission, Site::default())])->dispatch();
    }
}

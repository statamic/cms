<?php

namespace Tests\Forms;

use Illuminate\Support\Facades\Mail;
use Mockery;
use Statamic\Facades\Form as FacadesForm;
use Statamic\Forms\Email;
use Statamic\Forms\SendEmails;
use Statamic\Sites\Site;
use Tests\PreventSavingStacheItemsToDisk;
use Tests\TestCase;

class SendEmailsTest extends TestCase
{
    use PreventSavingStacheItemsToDisk;

    /** @test */
    public function it_sends_emails()
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

        (new SendEmails(
            $submission = $form->makeSubmission(),
            $site = Mockery::mock(Site::class)
        ))->handle();

        Mail::assertSent(Email::class, 2);

        Mail::assertSent(function (Email $mail) use ($submission, $site) {
            return $mail->getSubmission() === $submission
                && $mail->getSite() === $site
                && $mail->getConfig() === [
                    'from' => 'first@sender.com',
                    'to' => 'first@recipient.com',
                    'foo' => 'bar',
                    // test that the config is passed along unparsed.
                    // the email class will handle that. we don't want to double parse.
                    'unparsed' => '{{ test }}',
                ];
        });

        Mail::assertSent(function (Email $mail) use ($submission, $site) {
            return $mail->getSubmission() === $submission
                && $mail->getSite() === $site
                && $mail->getConfig() === [
                    'from' => 'second@sender.com',
                    'to' => 'second@recipient.com',
                    'baz' => 'qux',
                ];
        });
    }

    /** @test */
    public function it_sends_emails_when_config_contains_single_email()
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

        (new SendEmails(
            $submission = $form->makeSubmission(),
            $site = Mockery::mock(Site::class)
        ))->handle();

        Mail::assertSent(Email::class, 1);

        Mail::assertSent(function (Email $mail) use ($submission, $site) {
            return $mail->getSubmission() === $submission
                && $mail->getSite() === $site
                && $mail->getConfig() === [
                    'from' => 'first@sender.com',
                    'to' => 'first@recipient.com',
                    'foo' => 'bar',
                ];
        });
    }

    /**
     * @test
     * @dataProvider noEmailsProvider
     */
    public function no_emails_are_sent_if_none_are_configured($emailConfig)
    {
        Mail::fake();

        $form = tap(FacadesForm::make('test')->email($emailConfig))->save();

        (new SendEmails(
            $form->makeSubmission(),
            Mockery::mock(Site::class)
        ))->handle();

        Mail::assertNotSent(Email::class);
    }

    public function noEmailsProvider()
    {
        return [
            'null' => [null],
            'empty array' => [[]],
        ];
    }
}

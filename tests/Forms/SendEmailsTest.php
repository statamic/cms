<?php

namespace Tests\Forms;

use Illuminate\Support\Facades\Queue;
use Mockery;
use Statamic\Facades\Form as FacadesForm;
use Statamic\Forms\SendEmail;
use Statamic\Forms\SendEmails;
use Statamic\Sites\Site;
use Tests\PreventSavingStacheItemsToDisk;
use Tests\TestCase;

class SendEmailsTest extends TestCase
{
    use PreventSavingStacheItemsToDisk;

    /** @test */
    public function it_queues_email_jobs()
    {
        Queue::fake();

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

        Queue::assertPushed(SendEmail::class, 2);

        Queue::assertPushed(function (SendEmail $job) use ($submission, $site) {
            return $job->submission === $submission
                && $job->site === $site
                && $job->config === [
                    'from' => 'first@sender.com',
                    'to' => 'first@recipient.com',
                    'foo' => 'bar',
                    // test that the config is passed along unparsed.
                    // the email class will handle that. we don't want to double parse.
                    'unparsed' => '{{ test }}',
                ];
        });

        Queue::assertPushed(function (SendEmail $job) use ($submission, $site) {
            return $job->submission === $submission
                && $job->site === $site
                && $job->config === [
                    'from' => 'second@sender.com',
                    'to' => 'second@recipient.com',
                    'baz' => 'qux',
                ];
        });
    }

    /** @test */
    public function it_queues_email_jobs_when_config_contains_single_email()
    {
        // The email config should be an array of email configs.
        // e.g. [ [to,from,...], [to,from,...], ... ]
        // but it's possible that a user may have only one email config.
        // e.g. [to,from,...]

        Queue::fake();

        $form = tap(FacadesForm::make('test')->email([
            'from' => 'first@sender.com',
            'to' => 'first@recipient.com',
            'foo' => 'bar',
        ]))->save();

        (new SendEmails(
            $submission = $form->makeSubmission(),
            $site = Mockery::mock(Site::class)
        ))->handle();

        Queue::assertPushed(SendEmail::class, 1);

        Queue::assertPushed(function (SendEmail $job) use ($submission, $site) {
            return $job->submission === $submission
                && $job->site === $site
                && $job->config === [
                    'from' => 'first@sender.com',
                    'to' => 'first@recipient.com',
                    'foo' => 'bar',
                ];
        });
    }

    /**
     * @test
     *
     * @dataProvider noEmailsProvider
     */
    public function no_email_jobs_are_queued_if_none_are_configured($emailConfig)
    {
        Queue::fake();

        $form = tap(FacadesForm::make('test')->email($emailConfig))->save();

        (new SendEmails(
            $form->makeSubmission(),
            Mockery::mock(Site::class)
        ))->handle();

        Queue::assertNotPushed(SendEmail::class);
    }

    public static function noEmailsProvider()
    {
        return [
            'null' => [null],
            'empty array' => [[]],
        ];
    }
}

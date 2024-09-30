<?php

namespace Tests\Forms;

use Illuminate\Support\Facades\Mail;
use Mockery;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Facades\Form as FacadesForm;
use Statamic\Forms\Email;
use Statamic\Forms\SendEmail;
use Statamic\Sites\Site;
use Tests\PreventSavingStacheItemsToDisk;
use Tests\TestCase;

class SendEmailTest extends TestCase
{
    use PreventSavingStacheItemsToDisk;

    #[Test]
    public function it_sends_email()
    {
        Mail::fake();

        $form = tap(FacadesForm::make('test'))->save();

        (new SendEmail(
            $submission = $form->makeSubmission(),
            $site = Mockery::mock(Site::class)->shouldReceive('lang')->andReturn('en')->getMock(),
            [
                'from' => 'first@sender.com',
                'to' => 'first@recipient.com',
                'foo' => 'bar',
                'unparsed' => '{{ test }}',
            ]
        ))->handle();

        Mail::assertSent(Email::class, 1);

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
    }
}

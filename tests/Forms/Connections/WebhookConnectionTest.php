<?php

namespace Tests\Forms\Connections;

use Illuminate\Http\Client\Request;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Events\SubmissionFinalized;
use Statamic\Facades\Form;
use Statamic\Facades\Site;
use Statamic\Forms\Connections\Webhooks\DispatchWebhooks;
use Statamic\Forms\Connections\Webhooks\SendWebhook;
use Tests\PreventSavingStacheItemsToDisk;
use Tests\TestCase;

class WebhookConnectionTest extends TestCase
{
    use PreventSavingStacheItemsToDisk;

    #[Test]
    public function it_dispatches_a_job_per_webhook_config()
    {
        Bus::fake();

        $form = tap(Form::make('test')->connections(['webhook' => [
            ['url' => 'https://example.com/first'],
            ['url' => 'https://example.com/second'],
        ]]))->save();

        (new DispatchWebhooks)->handle(new SubmissionFinalized($form->makeSubmission()));

        Bus::assertDispatched(SendWebhook::class, 2);
        Bus::assertDispatched(SendWebhook::class, fn ($job) => $job->config['url'] === 'https://example.com/first');
        Bus::assertDispatched(SendWebhook::class, fn ($job) => $job->config['url'] === 'https://example.com/second');
    }

    #[Test]
    public function it_dispatches_webhooks_when_a_submission_is_finalized()
    {
        Bus::fake();

        $form = tap(Form::make('test')->connections(['webhook' => [
            ['url' => 'https://example.com/hook'],
        ]]))->save();

        $form->makeSubmission()->asPartial()->finalize();

        Bus::assertDispatched(SendWebhook::class, 1);
    }

    #[Test]
    public function it_skips_disabled_webhook_configs()
    {
        Bus::fake();

        $form = tap(Form::make('test')->connections(['webhook' => [
            ['url' => 'https://example.com/disabled', 'enabled' => false],
            ['url' => 'https://example.com/enabled', 'enabled' => true],
        ]]))->save();

        (new DispatchWebhooks)->handle(new SubmissionFinalized($form->makeSubmission()));

        Bus::assertDispatched(SendWebhook::class, 1);
        Bus::assertDispatched(SendWebhook::class, fn ($job) => $job->config['url'] === 'https://example.com/enabled');
    }

    #[Test]
    #[DataProvider('validUrlProvider')]
    public function it_posts_the_form_and_submission_as_json($url)
    {
        Http::fake();

        $form = tap(Form::make('test')->formFields([
            'fields' => [
                ['handle' => 'name', 'field' => ['type' => 'text']],
            ],
        ]))->save();

        $submission = $form->makeSubmission()->data(['name' => 'Gandalf']);

        (new SendWebhook($submission, Site::default(), ['url' => $url]))->handle();

        Http::assertSent(fn (Request $request) => $request->url() === $url
            && $request->method() === 'POST'
            && $request->isJson()
            && $request['form'] === 'test'
            && $request['submission']['name'] === 'Gandalf'
            && $request['submission']['id'] === $submission->id());
    }

    public static function validUrlProvider()
    {
        return [
            'https' => ['https://example.com/hook'],
            'http' => ['http://example.com/hook'],
        ];
    }

    #[Test]
    public function it_posts_without_ssl_verification_when_disabled()
    {
        Http::fake();

        $form = tap(Form::make('test'))->save();

        (new SendWebhook($form->makeSubmission(), Site::default(), [
            'url' => 'https://example.com/hook',
            'verify_ssl' => false,
        ]))->handle();

        Http::assertSentCount(1);
    }

    #[Test]
    public function it_throws_when_the_response_is_unsuccessful()
    {
        Http::fake(['*' => Http::response(null, 500)]);

        $form = tap(Form::make('test'))->save();

        $this->expectException(RequestException::class);

        (new SendWebhook($form->makeSubmission(), Site::default(), ['url' => 'https://example.com/hook']))->handle();
    }

    #[Test]
    #[DataProvider('invalidUrlProvider')]
    public function it_rejects_urls_without_an_http_scheme($url)
    {
        Http::fake();

        $form = tap(Form::make('test'))->save();

        try {
            (new SendWebhook($form->makeSubmission(), Site::default(), ['url' => $url]))->handle();
            $this->fail('An InvalidArgumentException should have been thrown.');
        } catch (InvalidArgumentException) {
        }

        Http::assertNothingSent();
    }

    public static function invalidUrlProvider()
    {
        return [
            'ftp' => ['ftp://example.com/hook'],
            'file' => ['file:///etc/passwd'],
            'schemeless' => ['example.com/hook'],
            'missing url' => [null],
        ];
    }

    #[Test]
    public function a_failing_webhook_on_the_sync_driver_stops_finalization_before_emails_are_sent()
    {
        Http::fake(['*' => Http::response(null, 500)]);
        Mail::fake();

        $form = tap(Form::make('test')->connections([
            'webhook' => [['url' => 'https://example.com/hook']],
            'email' => [['from' => 'sender@example.com', 'to' => 'recipient@example.com']],
        ]))->save();

        try {
            $form->makeSubmission()->asPartial()->finalize();
            $this->fail('A RequestException should have been thrown.');
        } catch (RequestException) {
        }

        Mail::assertNothingOutgoing();
    }
}

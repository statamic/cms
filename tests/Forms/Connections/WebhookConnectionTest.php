<?php

namespace Tests\Forms\Connections;

use Illuminate\Http\Client\Request;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Http;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Facades\Form;
use Statamic\Facades\Site;
use Statamic\Forms\Connections\Webhooks;
use Statamic\Forms\Connections\Webhooks\SendWebhook;
use Statamic\Forms\CreateAssetsFromFileUploads;
use Statamic\Forms\SendEmails;
use Tests\PreventSavingStacheItemsToDisk;
use Tests\TestCase;

class WebhookConnectionTest extends TestCase
{
    use PreventSavingStacheItemsToDisk;

    #[Test]
    public function it_returns_a_job_per_webhook_config()
    {
        $form = tap(Form::make('test')->connections(['webhook' => [
            ['url' => 'https://example.com/first'],
            ['url' => 'https://example.com/second'],
        ]]))->save();

        $jobs = (new Webhooks)->finalized($form->makeSubmission());

        $this->assertCount(2, $jobs);
        $this->assertContainsOnlyInstancesOf(SendWebhook::class, $jobs);
        $this->assertEquals('https://example.com/first', $jobs[0]->config['url']);
        $this->assertEquals('https://example.com/second', $jobs[1]->config['url']);
    }

    #[Test]
    public function it_skips_disabled_webhook_configs()
    {
        $form = tap(Form::make('test')->connections(['webhook' => [
            ['url' => 'https://example.com/disabled', 'enabled' => false],
            ['url' => 'https://example.com/enabled', 'enabled' => true],
        ]]))->save();

        $jobs = array_values((new Webhooks)->finalized($form->makeSubmission()));

        $this->assertCount(1, $jobs);
        $this->assertEquals('https://example.com/enabled', $jobs[0]->config['url']);
    }

    #[Test]
    #[DataProvider('webhookConditionsProvider')]
    public function it_filters_webhook_configs_using_conditions($conditions, $value, $shouldDispatch)
    {
        $form = tap(Form::make('test')->formFields([
            'fields' => [
                ['handle' => 'how_did_you_hear', 'field' => ['type' => 'text']],
            ],
        ])->connections(['webhook' => [
            ['url' => 'https://example.com/hook', 'conditions' => $conditions],
        ]]))->save();

        $submission = $form->makeSubmission()->data(['how_did_you_hear' => $value]);

        $this->assertCount($shouldDispatch ? 1 : 0, (new Webhooks)->finalized($submission));
    }

    public static function webhookConditionsProvider()
    {
        $conditions = [['field' => 'how_did_you_hear', 'operator' => 'equals', 'value' => 'friend', 'join' => 'and']];

        return [
            'no conditions' => [[], 'google', true],
            'matching conditions' => [$conditions, 'friend', true],
            'non-matching conditions' => [$conditions, 'google', false],
        ];
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
}

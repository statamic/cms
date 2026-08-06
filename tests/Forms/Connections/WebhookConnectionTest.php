<?php

namespace Tests\Forms\Connections;

use Illuminate\Http\Client\Request;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Facades\Form;
use Statamic\Facades\Site;
use Statamic\Facades\User;
use Statamic\Forms\Connections\Webhook;
use Statamic\Forms\Connections\Webhooks\SendWebhook;
use Tests\PreventSavingStacheItemsToDisk;
use Tests\TestCase;

class WebhookConnectionTest extends TestCase
{
    use PreventSavingStacheItemsToDisk;

    #[Test]
    public function it_returns_a_job_per_webhook()
    {
        $form = tap(Form::make('test')->connections(['webhook' => [
            ['url' => 'https://example.com/first'],
            ['url' => 'https://example.com/second'],
        ]]))->save();

        $jobs = (new Webhook)->finalized($form->makeSubmission());

        $this->assertCount(2, $jobs);
        $this->assertContainsOnlyInstancesOf(SendWebhook::class, $jobs);
        $this->assertEquals('https://example.com/first', $jobs[0]->config['url']);
        $this->assertEquals('https://example.com/second', $jobs[1]->config['url']);
    }

    #[Test]
    public function it_skips_disabled_webhooks()
    {
        $form = tap(Form::make('test')->connections(['webhook' => [
            ['url' => 'https://example.com/disabled', 'enabled' => false],
            ['url' => 'https://example.com/enabled', 'enabled' => true],
        ]]))->save();

        $jobs = array_values((new Webhook)->finalized($form->makeSubmission()));

        $this->assertCount(1, $jobs);
        $this->assertEquals('https://example.com/enabled', $jobs[0]->config['url']);
    }

    #[Test]
    #[DataProvider('webhookConditionsProvider')]
    public function it_filters_webhooks_using_conditions(array $conditions, string $value, bool $shouldDispatch)
    {
        $form = tap(Form::make('test')->formFields([
            'fields' => [
                ['handle' => 'how_did_you_hear', 'field' => ['type' => 'text']],
            ],
        ])->connections(['webhook' => [
            ['url' => 'https://example.com/hook', 'conditions' => $conditions],
        ]]))->save();

        $submission = $form->makeSubmission()->data(['how_did_you_hear' => $value]);

        $this->assertCount($shouldDispatch ? 1 : 0, (new Webhook)->finalized($submission));
    }

    public static function webhookConditionsProvider(): array
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
    public function it_posts_the_form_and_submission_as_json(string $url)
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

    public static function validUrlProvider(): array
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
    public function it_rejects_urls_without_an_http_scheme(?string $url)
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

    public static function invalidUrlProvider(): array
    {
        return [
            'ftp' => ['ftp://example.com/hook'],
            'file' => ['file:///etc/passwd'],
            'schemeless' => ['example.com/hook'],
            'missing url' => [null],
        ];
    }

    #[Test]
    #[DataProvider('webhookCountProvider')]
    public function it_counts_the_configured_webhooks(array $webhooks, int $count)
    {
        $form = tap(Form::make('test')->connections(['webhook' => $webhooks]))->save();

        $this->assertEquals($count, (new Webhook)->count($form));
    }

    public static function webhookCountProvider(): array
    {
        return [
            'none configured' => [[], 0],
            'one configured' => [[['id' => 'one', 'url' => 'https://example.com/first']], 1],
            'two configured' => [[['id' => 'one', 'url' => 'https://example.com/first'], ['id' => 'two', 'url' => 'https://example.com/second']], 2],
        ];
    }

    #[Test]
    public function it_renders_the_vue_component()
    {
        $this->actingAs(tap(User::make()->makeSuper())->save());

        $form = tap(Form::make('test')->formFields([
            'fields' => [
                ['handle' => 'full_name', 'field' => ['type' => 'name']],
            ],
        ])->connections(['webhook' => [
            ['id' => 'one', 'url' => 'https://example.com/first'],
            ['id' => 'two', 'url' => 'https://example.com/second', 'verify_ssl' => false],
        ]]))->save();

        $component = (new Webhook)->render($form)->toArray();

        $this->assertEquals('webhook-connection', $component['name']);
        $this->assertEquals(cp_route('forms.connect.webhook.update', 'test'), $component['props']['action']);
        $this->assertEquals(['one', 'two'], array_keys($component['props']['webhooks']));
        $this->assertEquals('https://example.com/first', $component['props']['webhooks']['one']['values']['url']);
        $this->assertTrue($component['props']['webhooks']['one']['values']['verify_ssl']);
        $this->assertEquals('https://example.com/second', $component['props']['webhooks']['two']['values']['url']);
        $this->assertFalse($component['props']['webhooks']['two']['values']['verify_ssl']);
        $this->assertNull($component['props']['defaults']['values']['url']);
        $this->assertArrayHasKey('meta', $component['props']['defaults']);
    }

    #[Test]
    public function the_example_payload_uses_the_latest_submission()
    {
        $this->actingAs(tap(User::make()->makeSuper())->save());

        $form = tap(Form::make('test')->formFields([
            'fields' => [
                ['handle' => 'full_name', 'field' => ['type' => 'name']],
            ],
        ]))->save();

        $this->assertEquals([
            'form' => 'test',
            'submission' => ['id' => '…', 'date' => '…', 'full_name' => 'Jamie Schmidt'],
        ], $this->examplePayload($form));

        $submission = tap($form->makeSubmission()->data(['full_name' => 'Gandalf']))->save();

        $this->assertEquals([
            'form' => 'test',
            'submission' => ['id' => $submission->id(), 'date' => $submission->date()->toJson(), 'full_name' => 'Gandalf'],
        ], $this->examplePayload($form));
    }

    private function examplePayload($form): array
    {
        $payload = (new Webhook)->render($form)->toArray()['props']['examplePayload'];

        $this->assertJson($payload);

        return json_decode($payload, true);
    }
}

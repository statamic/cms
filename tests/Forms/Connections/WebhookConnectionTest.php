<?php

namespace Tests\Forms\Connections;

use Illuminate\Http\Client\Request;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Validator;
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
        $this->assertEquals(['blueprint', 'meta', 'defaults', 'examplePayload'], array_keys($component['props']));
        $this->assertEquals(['one', 'two'], array_keys($component['props']['meta']));
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

        tap($form->makeSubmission()->data(['full_name' => 'Saruman'])->asPartial())->save();

        $this->assertEquals('Gandalf', $this->examplePayload($form)['submission']['full_name']);
    }

    private function examplePayload($form): array
    {
        $payload = (new Webhook)->render($form)->toArray()['props']['examplePayload'];

        $this->assertJson($payload);

        return json_decode($payload, true);
    }

    #[Test]
    public function it_pre_processes_webhook_configs()
    {
        $form = tap(Form::make('test')->connections(['webhook' => [
            [
                'id' => 'one',
                'url' => 'https://example.com/first',
                'conditions' => [['field' => 'name', 'operator' => 'equals', 'value' => 'Bob', 'join' => 'and']],
            ],
            ['id' => 'two', 'url' => 'https://example.com/second', 'verify_ssl' => false, 'enabled' => false],
        ]]))->save();

        $configs = (new Webhook)->preProcess($form->connections()->get('webhook'), $form);

        $this->assertEquals('one', $configs[0]['id']);
        $this->assertTrue($configs[0]['enabled']);
        $this->assertEquals('https://example.com/first', $configs[0]['url']);
        $this->assertTrue($configs[0]['verify_ssl']);
        $this->assertNotEmpty($configs[0]['conditions'][0]['_id']);
        $this->assertEquals('name', $configs[0]['conditions'][0]['field']);
        $this->assertEquals('Bob', $configs[0]['conditions'][0]['value']);
        $this->assertEquals('two', $configs[1]['id']);
        $this->assertFalse($configs[1]['enabled']);
        $this->assertEquals('https://example.com/second', $configs[1]['url']);
        $this->assertFalse($configs[1]['verify_ssl']);
        $this->assertEquals([], $configs[1]['conditions']);
    }

    #[Test]
    public function it_validates_webhook_configs()
    {
        $form = tap(Form::make('test'))->save();

        $validator = Validator::make([
            [
                'id' => 'abc',
                'url' => 'https://example.com/hook',
                'verify_ssl' => false,
                'enabled' => true,
                'conditions' => [['field' => 'name', 'operator' => 'equals', 'value' => 'Bob', 'join' => 'and']],
            ],
        ], (new Webhook)->rules($form));

        $this->assertTrue($validator->passes());
    }

    #[Test]
    #[DataProvider('invalidConfigs')]
    public function it_rejects_invalid_configs($configs, $errors)
    {
        $form = tap(Form::make('test'))->save();

        $validator = Validator::make($configs, (new Webhook)->rules($form));

        $this->assertTrue($validator->fails());

        foreach ($errors as $key) {
            $this->assertTrue($validator->errors()->has($key));
        }
    }

    public static function invalidConfigs(): array
    {
        return [
            'config not an array' => [['nope'], ['0']],
            'config without a url' => [[['enabled' => true]], ['0.url']],
            'config with an invalid url' => [[['url' => 'not a url']], ['0.url']],
            'config with a non-http url' => [[['url' => 'ftp://example.com/hook']], ['0.url']],
            'config with a non-boolean verify_ssl' => [[['url' => 'https://example.com/hook', 'verify_ssl' => 'nope']], ['0.verify_ssl']],
            'config with a non-boolean enabled' => [[['url' => 'https://example.com/hook', 'enabled' => 'nope']], ['0.enabled']],
            'config with non-array conditions' => [[['url' => 'https://example.com/hook', 'conditions' => 'nope']], ['0.conditions']],
            'config with a non-array condition' => [[['url' => 'https://example.com/hook', 'conditions' => ['nope']]], ['0.conditions.0']],
        ];
    }

    #[Test]
    public function it_processes_webhook_configs()
    {
        $form = tap(Form::make('test'))->save();

        $this->assertEquals([
            ['id' => 'def', 'url' => 'https://example.com/updated', 'enabled' => false, 'conditions' => [
                ['field' => 'name', 'operator' => 'equals', 'value' => 'Bob', 'join' => 'and'],
            ]],
            ['id' => 'ghi', 'url' => 'http://localhost:5678/n8n', 'verify_ssl' => false],
            ['id' => 'jkl', 'url' => 'https://example.com/defaults'],
        ], (new Webhook)->process([
            ['id' => 'def', 'url' => 'https://example.com/updated', 'enabled' => false, 'conditions' => [
                ['_id' => 'vue-row', 'field' => 'name', 'operator' => 'equals', 'value' => 'Bob', 'join' => 'and'],
            ]],
            ['id' => 'ghi', 'url' => 'http://localhost:5678/n8n', 'verify_ssl' => false],
            ['id' => 'jkl', 'url' => 'https://example.com/defaults', 'enabled' => true, 'verify_ssl' => true],
        ], $form));
    }

    #[Test]
    public function it_doesnt_persist_the_client_row_state()
    {
        $form = tap(Form::make('test'))->save();

        $this->assertEquals([
            ['id' => 'abc', 'url' => 'https://example.com/hook'],
        ], (new Webhook)->process([
            ['_id' => 'vue-row', 'id' => 'abc', 'url' => 'https://example.com/hook'],
        ], $form));
    }

    #[Test]
    public function it_generates_an_id_for_configs_that_dont_have_one()
    {
        $form = tap(Form::make('test'))->save();

        $config = (new Webhook)->process([['url' => 'https://example.com/hook']], $form)[0];

        $this->assertNotEmpty($config['id']);
        $this->assertEquals('https://example.com/hook', $config['url']);
    }

    #[Test]
    public function it_removes_null_values_from_configs()
    {
        $form = tap(Form::make('test'))->save();

        $this->assertEquals([[
            'id' => 'abc',
            'url' => 'https://example.com/hook',
        ]], (new Webhook)->process([[
            'id' => 'abc',
            'url' => 'https://example.com/hook',
            'enabled' => null,
        ]], $form));
    }
}

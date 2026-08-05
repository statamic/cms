<?php

namespace Tests\Feature\Forms;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Facades\Form;
use Statamic\Facades\User;
use Tests\FakesRoles;
use Tests\PreventSavingStacheItemsToDisk;
use Tests\TestCase;

class UpdateWebhookConnectionTest extends TestCase
{
    use FakesRoles;
    use PreventSavingStacheItemsToDisk;

    protected function resolveApplicationConfiguration($app)
    {
        parent::resolveApplicationConfiguration($app);

        $app['config']['statamic.forms.forms'] = $this->fakeStacheDirectory.'/forms';
    }

    #[Test]
    public function it_denies_access_if_you_dont_have_permission()
    {
        $form = tap(Form::make('test')->connections([
            'webhook' => [['id' => 'abc', 'url' => 'https://example.com/hook']],
        ]))->save();

        $this
            ->from('/original')
            ->actingAs($this->userWithoutPermission())
            ->update($form, ['webhooks' => []])
            ->assertRedirect('/original')
            ->assertSessionHas('error');

        $this->assertEquals([['id' => 'abc', 'url' => 'https://example.com/hook']], Form::find('test')->connections()->get('webhook'));
    }

    #[Test]
    public function it_updates_the_webhook_configs()
    {
        $form = tap(Form::make('test')->connections([
            'email' => [['id' => 'abc', 'to' => 'foo@example.com']],
            'webhook' => [['id' => 'def', 'url' => 'https://example.com/hook']],
        ]))->save();

        $this
            ->actingAs($this->userWithPermission())
            ->update($form, ['webhooks' => [
                ['id' => 'def', 'url' => 'https://example.com/updated', 'enabled' => false],
                ['id' => 'ghi', 'url' => 'http://localhost:5678/n8n', 'verify_ssl' => false],
                ['id' => 'jkl', 'url' => 'https://example.com/defaults', 'enabled' => true, 'verify_ssl' => true],
            ]])
            ->assertOk();

        $updated = Form::find('test');
        $this->assertEquals([
            ['id' => 'def', 'url' => 'https://example.com/updated', 'enabled' => false],
            ['id' => 'ghi', 'url' => 'http://localhost:5678/n8n', 'verify_ssl' => false],
            ['id' => 'jkl', 'url' => 'https://example.com/defaults'],
        ], $updated->connections()->get('webhook'));
        $this->assertEquals([['id' => 'abc', 'to' => 'foo@example.com']], $updated->connections()->get('email'));
    }

    #[Test]
    public function it_doesnt_persist_the_client_row_state()
    {
        $form = tap(Form::make('test'))->save();

        $this
            ->actingAs($this->userWithPermission())
            ->update($form, ['webhooks' => [['_id' => 'vue-row', 'id' => 'abc', 'url' => 'https://example.com/hook']]])
            ->assertOk();

        $this->assertEquals([
            ['id' => 'abc', 'url' => 'https://example.com/hook'],
        ], Form::find('test')->connections()->get('webhook'));
    }

    #[Test]
    public function it_generates_an_id_for_configs_that_dont_have_one()
    {
        $form = tap(Form::make('test'))->save();

        $this
            ->actingAs($this->userWithPermission())
            ->update($form, ['webhooks' => [['url' => 'https://example.com/hook']]])
            ->assertOk();

        $config = Form::find('test')->connections()->get('webhook')[0];

        $this->assertNotEmpty($config['id']);
        $this->assertEquals('https://example.com/hook', $config['url']);
    }

    #[Test]
    public function it_normalizes_the_conditions()
    {
        $form = tap(Form::make('test'))->save();

        $this
            ->actingAs($this->userWithPermission())
            ->update($form, ['webhooks' => [
                [
                    'id' => 'abc',
                    'url' => 'https://example.com/hook',
                    'conditions' => [
                        ['_id' => 'vue-row', 'field' => 'name', 'operator' => 'equals', 'value' => 'Bob', 'join' => 'and'],
                        ['field' => null, 'operator' => 'equals', 'value' => 'incomplete', 'join' => 'and'],
                    ],
                ],
                [
                    'id' => 'def',
                    'url' => 'https://example.com/other',
                    'conditions' => [],
                ],
            ]])
            ->assertOk();

        $this->assertEquals([
            [
                'id' => 'abc',
                'url' => 'https://example.com/hook',
                'conditions' => [
                    ['field' => 'name', 'operator' => 'equals', 'value' => 'Bob', 'join' => 'and'],
                ],
            ],
            [
                'id' => 'def',
                'url' => 'https://example.com/other',
            ],
        ], Form::find('test')->connections()->get('webhook'));
    }

    #[Test]
    public function it_removes_null_values_from_configs()
    {
        $form = tap(Form::make('test'))->save();

        $this
            ->actingAs($this->userWithPermission())
            ->update($form, ['webhooks' => [[
                'id' => 'abc',
                'url' => 'https://example.com/hook',
                'enabled' => null,
            ]]])
            ->assertOk();

        $this->assertEquals([[
            'id' => 'abc',
            'url' => 'https://example.com/hook',
        ]], Form::find('test')->connections()->get('webhook'));
    }

    #[Test]
    #[DataProvider('invalidPayloads')]
    public function it_rejects_invalid_payloads($payload, $errors)
    {
        $form = tap(Form::make('test')->connections([
            'webhook' => [['id' => 'abc', 'url' => 'https://example.com/hook']],
        ]))->save();

        $this
            ->actingAs($this->userWithPermission())
            ->updateJson($form, $payload)
            ->assertUnprocessable()
            ->assertJsonValidationErrors($errors);

        $this->assertEquals([['id' => 'abc', 'url' => 'https://example.com/hook']], Form::find('test')->connections()->get('webhook'));
    }

    public static function invalidPayloads()
    {
        return [
            'missing configs' => [[], ['webhooks']],
            'webhooks not an array' => [['webhooks' => 'nope'], ['webhooks']],
            'config not an array' => [['webhooks' => ['nope']], ['webhooks.0']],
            'config without a url' => [['webhooks' => [['enabled' => true]]], ['webhooks.0.url']],
            'config with an invalid url' => [['webhooks' => [['url' => 'not a url']]], ['webhooks.0.url']],
            'config with a non-http url' => [['webhooks' => [['url' => 'ftp://example.com/hook']]], ['webhooks.0.url']],
            'config with a non-boolean verify_ssl' => [['webhooks' => [['url' => 'https://example.com/hook', 'verify_ssl' => 'nope']]], ['webhooks.0.verify_ssl']],
        ];
    }

    private function userWithoutPermission()
    {
        $this->setTestRoles(['test' => ['access cp']]);

        return tap(User::make()->assignRole('test'))->save();
    }

    private function userWithPermission()
    {
        $this->setTestRoles(['test' => ['access cp', 'edit forms']]);

        return tap(User::make()->assignRole('test'))->save();
    }

    private function update($form, $params = [])
    {
        return $this->patch(cp_route('forms.connect.webhook.update', $form->handle()), $params);
    }

    private function updateJson($form, $params = [])
    {
        return $this->patchJson(cp_route('forms.connect.webhook.update', $form->handle()), $params);
    }
}

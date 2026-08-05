<?php

namespace Tests\Feature\Forms;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Facades\Form;
use Statamic\Facades\User;
use Tests\FakesRoles;
use Tests\PreventSavingStacheItemsToDisk;
use Tests\TestCase;

class UpdateEmailConnectionTest extends TestCase
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
            'email' => [['id' => 'abc', 'to' => 'old@example.com']],
        ]))->save();

        $this
            ->from('/original')
            ->actingAs($this->userWithoutPermission())
            ->update($form, ['emails' => []])
            ->assertRedirect('/original')
            ->assertSessionHas('error');

        $this->assertEquals([['id' => 'abc', 'to' => 'old@example.com']], Form::find('test')->connections()->get('email'));
    }

    #[Test]
    public function it_updates_the_email_configs()
    {
        $form = tap(Form::make('test')->formFields([
            'fields' => [
                ['handle' => 'email', 'field' => ['type' => 'email']],
            ],
        ])->connections([
            'email' => [['id' => 'abc', 'to' => 'old@example.com']],
            'webhook' => [['id' => 'def', 'url' => 'https://example.com/hook']],
        ]))->save();

        $this
            ->actingAs($this->userWithPermission())
            ->update($form, ['emails' => [
                ['id' => 'abc', 'to' => ['new@example.com', 'field:email'], 'subject' => 'Updated'],
                ['id' => 'ghi', 'to' => ['another@example.com']],
            ]])
            ->assertOk();

        $updated = Form::find('test');
        $this->assertEquals([
            ['id' => 'abc', 'to' => ['new@example.com', 'field:email'], 'subject' => 'Updated'],
            ['id' => 'ghi', 'to' => ['another@example.com']],
        ], $updated->connections()->get('email'));
        $this->assertEquals([['id' => 'def', 'url' => 'https://example.com/hook']], $updated->connections()->get('webhook'));
    }

    #[Test]
    public function it_generates_an_id_for_configs_that_dont_have_one()
    {
        $form = tap(Form::make('test'))->save();

        $this
            ->actingAs($this->userWithPermission())
            ->update($form, ['emails' => [['to' => ['foo@example.com']]]])
            ->assertOk();

        $config = Form::find('test')->connections()->get('email')[0];

        $this->assertNotEmpty($config['id']);
        $this->assertEquals(['foo@example.com'], $config['to']);
    }

    #[Test]
    public function it_normalizes_the_configs()
    {
        $form = tap(Form::make('test'))->save();

        $this
            ->actingAs($this->userWithPermission())
            ->update($form, ['emails' => [[
                '_id' => 'vue-row',
                'id' => 'abc',
                'to' => ['foo@example.com'],
                'cc' => [],
                'subject' => '',
                'reply_to' => null,
                'markdown' => false,
                'attachments' => true,
                'enabled' => true,
            ]]])
            ->assertOk();

        $this->assertEquals([[
            'id' => 'abc',
            'to' => ['foo@example.com'],
            'attachments' => true,
        ]], Form::find('test')->connections()->get('email'));
    }

    #[Test]
    public function it_normalizes_the_conditions()
    {
        $form = tap(Form::make('test'))->save();

        $this
            ->actingAs($this->userWithPermission())
            ->update($form, ['emails' => [
                [
                    'id' => 'abc',
                    'to' => ['foo@example.com'],
                    'conditions' => [
                        ['_id' => 'vue-row', 'field' => 'name', 'operator' => 'equals', 'value' => 'Bob', 'join' => 'and'],
                        ['field' => null, 'operator' => 'equals', 'value' => 'incomplete', 'join' => 'and'],
                        ['field' => 'name', 'operator' => 'equals', 'value' => '', 'join' => 'and'],
                    ],
                ],
                [
                    'id' => 'def',
                    'to' => ['bar@example.com'],
                    'conditions' => [],
                ],
            ]])
            ->assertOk();

        $this->assertEquals([
            [
                'id' => 'abc',
                'to' => ['foo@example.com'],
                'conditions' => [
                    ['field' => 'name', 'operator' => 'equals', 'value' => 'Bob', 'join' => 'and'],
                ],
            ],
            [
                'id' => 'def',
                'to' => ['bar@example.com'],
            ],
        ], Form::find('test')->connections()->get('email'));
    }

    #[Test]
    #[DataProvider('invalidPayloads')]
    public function it_rejects_invalid_payloads($payload, $errors)
    {
        $form = tap(Form::make('test')->connections([
            'email' => [['id' => 'abc', 'to' => 'old@example.com']],
        ]))->save();

        $this
            ->actingAs($this->userWithPermission())
            ->updateJson($form, $payload)
            ->assertUnprocessable()
            ->assertJsonValidationErrors($errors);

        $this->assertEquals([['id' => 'abc', 'to' => 'old@example.com']], Form::find('test')->connections()->get('email'));
    }

    public static function invalidPayloads()
    {
        return [
            'missing configs' => [[], ['emails']],
            'configs not an array' => [['emails' => 'nope'], ['emails']],
            'config not an array' => [['emails' => ['nope']], ['emails.0']],
            'config without a recipient' => [['emails' => [['from' => 'foo@example.com']]], ['emails.0.to']],
            'config with an empty recipient list' => [['emails' => [['to' => []]]], ['emails.0.to']],
            'config with an invalid recipient' => [['emails' => [['to' => ['not-an-email']]]], ['emails.0.to']],
            'config with an invalid legacy string recipient' => [['emails' => [['to' => 'foo@example.com, not-an-email']]], ['emails.0.to']],
            'config with an unknown field reference' => [['emails' => [['to' => ['field:unknown']]]], ['emails.0.to']],
            'config with an invalid cc' => [['emails' => [['to' => ['foo@example.com'], 'cc' => ['not-an-email']]]], ['emails.0.cc']],
            'config with an invalid sender' => [['emails' => [['to' => ['foo@example.com'], 'from' => 'not-an-email']]], ['emails.0.from']],
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
        return $this->patch(cp_route('forms.connect.email.update', $form->handle()), $params);
    }

    private function updateJson($form, $params = [])
    {
        return $this->patchJson(cp_route('forms.connect.email.update', $form->handle()), $params);
    }
}

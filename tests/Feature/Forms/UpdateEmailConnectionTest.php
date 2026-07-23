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
            ->update($form, ['configs' => []])
            ->assertRedirect('/original')
            ->assertSessionHas('error');

        $this->assertEquals([['id' => 'abc', 'to' => 'old@example.com']], Form::find('test')->connections()->get('email'));
    }

    #[Test]
    public function it_updates_the_email_configs()
    {
        $form = tap(Form::make('test')->connections([
            'email' => [['id' => 'abc', 'to' => 'old@example.com']],
            'webhook' => [['id' => 'def', 'url' => 'https://example.com/hook']],
        ]))->save();

        $this
            ->actingAs($this->userWithPermission())
            ->update($form, ['configs' => [
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
    public function it_normalizes_the_configs()
    {
        $form = tap(Form::make('test'))->save();

        $this
            ->actingAs($this->userWithPermission())
            ->update($form, ['configs' => [[
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
            ->update($form, ['configs' => [
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
            'missing configs' => [[], ['configs']],
            'configs not an array' => [['configs' => 'nope'], ['configs']],
            'config not an array' => [['configs' => ['nope']], ['configs.0']],
            'config without a recipient' => [['configs' => [['from' => ['foo@example.com']]]], ['configs.0.to']],
            'config with an empty recipient list' => [['configs' => [['to' => []]]], ['configs.0.to']],
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

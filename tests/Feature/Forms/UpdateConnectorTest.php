<?php

namespace Tests\Feature\Forms;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Facades\Form;
use Statamic\Facades\User;
use Tests\FakesRoles;
use Tests\PreventSavingStacheItemsToDisk;
use Tests\TestCase;

class UpdateConnectorTest extends TestCase
{
    use FakesRoles;
    use PreventSavingStacheItemsToDisk;

    protected function resolveApplicationConfiguration($app)
    {
        parent::resolveApplicationConfiguration($app);

        $app['config']['statamic.forms.forms'] = $this->fakeStacheDirectory.'/forms';
    }

    #[Test]
    public function it_updates_the_connections()
    {
        $this->setTestRoles(['test' => ['access cp', 'edit forms', 'view form submissions']]);
        $user = tap(User::make()->assignRole('test'))->save();
        $form = tap(Form::make('test')->connections([
            'email' => [['id' => 'abc', 'to' => ['old@example.com']]],
            'webhook' => [['id' => 'def', 'url' => 'https://example.com/hook']],
        ]))->save();

        $this
            ->actingAs($user)
            ->patchJson(cp_route('forms.connect.update', [$form->handle(), 'email']), [
                ['id' => 'abc', 'to' => ['new@example.com'], 'subject' => 'Updated'],
                ['id' => 'ghi', 'to' => ['another@example.com']],
            ])
            ->assertOk()
            ->assertJsonCount(2)
            ->assertJsonPath('0.id', 'abc')
            ->assertJsonPath('0.subject', 'Updated')
            ->assertJsonPath('1.id', 'ghi');

        $updated = Form::find('test');
        $this->assertEquals([
            ['id' => 'abc', 'to' => ['new@example.com'], 'subject' => 'Updated'],
            ['id' => 'ghi', 'to' => ['another@example.com']],
        ], $updated->connections()->get('email'));
        $this->assertEquals([['id' => 'def', 'url' => 'https://example.com/hook']], $updated->connections()->get('webhook'));
    }

    #[Test]
    public function it_returns_the_pre_processed_values_after_saving()
    {
        $this->setTestRoles(['test' => ['access cp', 'edit forms', 'view form submissions']]);
        $user = tap(User::make()->assignRole('test'))->save();
        $form = tap(Form::make('test'))->save();

        $response = $this
            ->actingAs($user)
            ->patchJson(cp_route('forms.connect.update', [$form->handle(), 'email']), [
                [
                    'id' => 'abc',
                    'to' => ['new@example.com'],
                    'conditions' => [
                        ['_id' => 'client', 'field' => 'name', 'operator' => 'equals', 'value' => 'Foo'],
                        ['_id' => 'incomplete', 'field' => '', 'operator' => 'equals', 'value' => 'Foo'],
                    ],
                ],
            ])
            ->assertOk();

        $conditions = $response->json('0.conditions');
        $this->assertCount(1, $conditions);
        $this->assertNotEmpty($conditions[0]['_id']);
        $this->assertNotEquals('client', $conditions[0]['_id']);
        $this->assertTrue($response->json('0.enabled'));
        $this->assertArrayNotHasKey('_id', Form::find('test')->connections()->get('email')[0]['conditions'][0]);
    }

    #[Test]
    public function it_validates_and_returns_the_pre_processed_values_without_saving_when_save_is_false()
    {
        $this->setTestRoles(['test' => ['access cp', 'edit forms', 'view form submissions']]);
        $user = tap(User::make()->assignRole('test'))->save();
        $form = tap(Form::make('test')->connections([
            'email' => [['id' => 'abc', 'to' => ['old@example.com']]],
        ]))->save();

        $response = $this
            ->actingAs($user)
            ->patchJson(cp_route('forms.connect.update', [$form->handle(), 'email']).'?_save=false', [
                [
                    'id' => 'abc',
                    'to' => ['new@example.com'],
                    'subject' => 'Updated',
                    'conditions' => [['_id' => 'client', 'field' => 'name', 'operator' => 'equals', 'value' => 'Foo']],
                ],
            ])
            ->assertOk();

        $this->assertEquals('abc', $response->json('0.id'));
        $this->assertEquals(['new@example.com'], $response->json('0.to'));
        $this->assertEquals('Updated', $response->json('0.subject'));
        $this->assertTrue($response->json('0.enabled'));
        $this->assertCount(1, $response->json('0.conditions'));
        $this->assertNotEquals('client', $response->json('0.conditions.0._id'));

        $this->assertEquals(
            [['id' => 'abc', 'to' => ['old@example.com']]],
            Form::find('test')->connections()->get('email')
        );
    }

    #[Test]
    public function it_clears_the_connections_config_when_saving_an_empty_array()
    {
        $this->setTestRoles(['test' => ['access cp', 'edit forms', 'view form submissions']]);
        $user = tap(User::make()->assignRole('test'))->save();
        $form = tap(Form::make('test')->connections([
            'email' => [['id' => 'abc', 'to' => ['old@example.com']]],
        ]))->save();

        $this
            ->actingAs($user)
            ->patchJson(cp_route('forms.connect.update', [$form->handle(), 'email']), [])
            ->assertOk();

        $this->assertEmpty(Form::find('test')->connections()->get('email'));
    }

    #[Test]
    public function it_rejects_an_invalid_payload()
    {
        $this->setTestRoles(['test' => ['access cp', 'edit forms', 'view form submissions']]);
        $user = tap(User::make()->assignRole('test'))->save();
        $form = tap(Form::make('test')->connections([
            'email' => [['id' => 'abc', 'to' => ['old@example.com']]],
        ]))->save();

        $this
            ->actingAs($user)
            ->patchJson(cp_route('forms.connect.update', [$form->handle(), 'email']), [['from' => 'foo@example.com']])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['0.to']);

        $this->assertEquals([['id' => 'abc', 'to' => ['old@example.com']]], Form::find('test')->connections()->get('email'));
    }

    #[Test]
    public function it_404s_if_the_connector_doesnt_exist()
    {
        $this->setTestRoles(['test' => ['access cp', 'edit forms', 'view form submissions']]);
        $user = tap(User::make()->assignRole('test'))->save();
        $form = tap(Form::make('test'))->save();

        $this
            ->actingAs($user)
            ->patchJson(cp_route('forms.connect.update', [$form->handle(), 'unknown']), [])
            ->assertNotFound();
    }

    #[Test]
    #[DataProvider('editOnlyPermissionsProvider')]
    public function it_denies_access_with_only_edit_permissions(array $permissions)
    {
        $this->setTestRoles(['test' => ['access cp', ...$permissions]]);
        $user = tap(User::make()->assignRole('test'))->save();
        $form = tap(Form::make('test')->connections([
            'email' => [['id' => 'abc', 'to' => ['old@example.com']]],
        ]))->save();

        $this
            ->actingAs($user)
            ->patchJson(cp_route('forms.connect.update', [$form->handle(), 'email']), [
                ['id' => 'abc', 'to' => ['attacker@example.com'], 'attachments' => true],
            ])
            ->assertForbidden();

        $this
            ->actingAs($user)
            ->patchJson(cp_route('forms.connect.update', [$form->handle(), 'webhook']), [
                ['url' => 'https://attacker.example.com/hook'],
            ])
            ->assertForbidden();

        $this->assertEquals([['id' => 'abc', 'to' => ['old@example.com']]], Form::find('test')->connections()->get('email'));
        $this->assertNull(Form::find('test')->connections()->get('webhook'));
    }

    public static function editOnlyPermissionsProvider(): array
    {
        return [
            'edit forms' => [['edit forms']],
            'edit test form' => [['edit test form']],
        ];
    }

    #[Test]
    public function it_updates_the_connections_with_per_form_permissions()
    {
        $this->setTestRoles(['test' => ['access cp', 'edit test form', 'view test form submissions']]);
        $user = tap(User::make()->assignRole('test'))->save();
        $form = tap(Form::make('test'))->save();

        $this
            ->actingAs($user)
            ->patchJson(cp_route('forms.connect.update', [$form->handle(), 'email']), [
                ['id' => 'abc', 'to' => ['new@example.com']],
            ])
            ->assertOk();

        $this->assertEquals([['id' => 'abc', 'to' => ['new@example.com']]], Form::find('test')->connections()->get('email'));
    }

    #[Test]
    #[DataProvider('templateSyntaxProvider')]
    public function it_rejects_template_syntax_from_untrusted_users(string $key, mixed $value)
    {
        $this->setTestRoles(['test' => ['access cp', 'edit forms', 'view form submissions']]);
        $user = tap(User::make()->assignRole('test'))->save();
        $form = tap(Form::make('test')->connections([
            'email' => [['id' => 'abc', 'to' => ['old@example.com']]],
        ]))->save();

        $this
            ->actingAs($user)
            ->patchJson(cp_route('forms.connect.update', [$form->handle(), 'email']), [
                ['id' => 'abc', 'to' => ['old@example.com'], $key => $value],
            ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(["0.{$key}"]);

        $this->assertEquals([['id' => 'abc', 'to' => ['old@example.com']]], Form::find('test')->connections()->get('email'));
    }

    public static function templateSyntaxProvider(): array
    {
        return [
            'antlers in the subject' => ['subject', 'Hi {{ config:app.name }}'],
            'antlers without spaces' => ['subject', 'Hi {{config:app.name}}'],
            'env in the subject' => ['subject', 'Hi {env:APP_KEY}'],
            'antlers in a recipient' => ['to', ['old@example.com', '{{ users }}{{ email }},{{ /users }}']],
            'antlers in a legacy recipient string' => ['to', 'old@example.com, {{ config:mail.from.address }}'],
            'antlers in the bcc' => ['bcc', ['{{ config:mail.from.address }}']],
            'antlers in the id' => ['id', '{{ config:app.name }}'],
        ];
    }

    #[Test]
    public function it_allows_unchanged_template_syntax_from_untrusted_users()
    {
        $this->setTestRoles(['test' => ['access cp', 'edit forms', 'view form submissions']]);
        $user = tap(User::make()->assignRole('test'))->save();
        $form = tap(Form::make('test')->connections([
            'email' => [
                ['id' => 'abc', 'to' => ['{{ config:mail.from.address }}'], 'subject' => 'Hi {{ config:app.name }}'],
                ['id' => 'def', 'to' => '{{ config:mail.from.address }}, sales@example.com'],
            ],
        ]))->save();

        $this
            ->actingAs($user)
            ->patchJson(cp_route('forms.connect.update', [$form->handle(), 'email']), [
                ['id' => 'abc', 'to' => ['{{ config:mail.from.address }}', 'new@example.com'], 'subject' => 'Hi {{ config:app.name }}', 'attachments' => true],
                ['id' => 'def', 'to' => ['{{ config:mail.from.address }}', 'sales@example.com', 'more@example.com']],
            ])
            ->assertOk();

        $this->assertEquals([
            ['id' => 'abc', 'to' => ['{{ config:mail.from.address }}', 'new@example.com'], 'subject' => 'Hi {{ config:app.name }}', 'attachments' => true],
            ['id' => 'def', 'to' => ['{{ config:mail.from.address }}', 'sales@example.com', 'more@example.com']],
        ], Form::find('test')->connections()->get('email'));
    }

    #[Test]
    public function it_rejects_stored_template_syntax_moved_to_another_key_or_connection()
    {
        $this->setTestRoles(['test' => ['access cp', 'edit forms', 'view form submissions']]);
        $user = tap(User::make()->assignRole('test'))->save();
        $form = tap(Form::make('test')->connections([
            'email' => [['id' => 'abc', 'to' => ['old@example.com'], 'subject' => 'Hi {{ config:app.name }}']],
        ]))->save();

        $this
            ->actingAs($user)
            ->patchJson(cp_route('forms.connect.update', [$form->handle(), 'email']), [
                ['id' => 'abc', 'to' => ['old@example.com'], 'subject' => 'Hi {{ config:app.name }}', 'from' => 'Hi {{ config:app.name }}'],
                ['id' => 'def', 'to' => ['old@example.com'], 'subject' => 'Hi {{ config:app.name }}'],
            ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['0.from', '1.subject'])
            ->assertJsonMissingValidationErrors(['0.subject']);
    }

    #[Test]
    #[DataProvider('trustedPermissionsProvider')]
    public function it_allows_template_syntax_from_trusted_users(array $permissions, bool $super)
    {
        $this->setTestRoles(['test' => ['access cp', ...$permissions]]);
        $user = User::make()->assignRole('test');
        $super ? $user->makeSuper() : null;
        $user->save();
        $form = tap(Form::make('test'))->save();

        $this
            ->actingAs($user)
            ->patchJson(cp_route('forms.connect.update', [$form->handle(), 'email']), [
                ['id' => 'abc', 'to' => ['{{ config:mail.from.address }}'], 'subject' => 'Hi {{ config:app.name }} {env:APP_NAME}'],
            ])
            ->assertOk();

        $this->assertEquals(
            [['id' => 'abc', 'to' => ['{{ config:mail.from.address }}'], 'subject' => 'Hi {{ config:app.name }} {env:APP_NAME}']],
            Form::find('test')->connections()->get('email')
        );
    }

    public static function trustedPermissionsProvider(): array
    {
        return [
            'configure forms' => [['configure forms'], false],
            'super user' => [[], true],
        ];
    }

    #[Test]
    public function it_denies_access_if_you_dont_have_permission()
    {
        $this->setTestRoles(['test' => ['access cp']]);
        $user = tap(User::make()->assignRole('test'))->save();
        $form = tap(Form::make('test')->connections([
            'email' => [['id' => 'abc', 'to' => ['old@example.com']]],
        ]))->save();

        $this
            ->from('/original')
            ->actingAs($user)
            ->patch(cp_route('forms.connect.update', [$form->handle(), 'email']), [])
            ->assertRedirect('/original')
            ->assertSessionHas('error');

        $this->assertEquals([['id' => 'abc', 'to' => ['old@example.com']]], Form::find('test')->connections()->get('email'));
    }
}

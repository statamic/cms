<?php

namespace Tests\Feature\Forms;

use PHPUnit\Framework\Attributes\Test;
use Statamic\Facades\Form;
use Statamic\Facades\User;
use Tests\FakesRoles;
use Tests\PreventSavingStacheItemsToDisk;
use Tests\TestCase;

class UpdateConnectionTest extends TestCase
{
    use FakesRoles;
    use PreventSavingStacheItemsToDisk;

    protected function resolveApplicationConfiguration($app)
    {
        parent::resolveApplicationConfiguration($app);

        $app['config']['statamic.forms.forms'] = $this->fakeStacheDirectory.'/forms';
    }

    #[Test]
    public function it_updates_the_connection()
    {
        $this->setTestRoles(['test' => ['access cp', 'edit forms']]);
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
            ->assertExactJson([
                ['id' => 'abc', 'to' => ['new@example.com'], 'subject' => 'Updated'],
                ['id' => 'ghi', 'to' => ['another@example.com']],
            ]);

        $updated = Form::find('test');
        $this->assertEquals([
            ['id' => 'abc', 'to' => ['new@example.com'], 'subject' => 'Updated'],
            ['id' => 'ghi', 'to' => ['another@example.com']],
        ], $updated->connections()->get('email'));
        $this->assertEquals([['id' => 'def', 'url' => 'https://example.com/hook']], $updated->connections()->get('webhook'));
    }

    #[Test]
    public function it_clears_the_connections_config_when_saving_an_empty_array()
    {
        $this->setTestRoles(['test' => ['access cp', 'edit forms']]);
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
        $this->setTestRoles(['test' => ['access cp', 'edit forms']]);
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
    public function it_404s_if_the_connection_doesnt_exist()
    {
        $this->setTestRoles(['test' => ['access cp', 'edit forms']]);
        $user = tap(User::make()->assignRole('test'))->save();
        $form = tap(Form::make('test'))->save();

        $this
            ->actingAs($user)
            ->patchJson(cp_route('forms.connect.update', [$form->handle(), 'unknown']), [])
            ->assertNotFound();
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

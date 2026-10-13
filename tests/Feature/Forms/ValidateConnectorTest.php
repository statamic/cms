<?php

namespace Tests\Feature\Forms;

use PHPUnit\Framework\Attributes\Test;
use Statamic\Facades\Form;
use Statamic\Facades\User;
use Tests\FakesRoles;
use Tests\PreventSavingStacheItemsToDisk;
use Tests\TestCase;

class ValidateConnectorTest extends TestCase
{
    use FakesRoles;
    use PreventSavingStacheItemsToDisk;

    protected function resolveApplicationConfiguration($app)
    {
        parent::resolveApplicationConfiguration($app);

        $app['config']['statamic.forms.forms'] = $this->fakeStacheDirectory.'/forms';
    }

    #[Test]
    public function it_returns_the_pre_processed_values_without_saving()
    {
        $this->setTestRoles(['test' => ['access cp', 'edit forms']]);
        $user = tap(User::make()->assignRole('test'))->save();
        $form = tap(Form::make('test')->connections([
            'email' => [['id' => 'abc', 'to' => ['old@example.com']]],
        ]))->save();

        $response = $this
            ->actingAs($user)
            ->postJson(cp_route('forms.connect.validate', [$form->handle(), 'email']), [
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
    public function it_rejects_an_invalid_payload()
    {
        $this->setTestRoles(['test' => ['access cp', 'edit forms']]);
        $user = tap(User::make()->assignRole('test'))->save();
        $form = tap(Form::make('test'))->save();

        $this
            ->actingAs($user)
            ->postJson(cp_route('forms.connect.validate', [$form->handle(), 'email']), [['from' => 'foo@example.com']])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['0.to']);
    }

    #[Test]
    public function it_404s_if_the_connector_doesnt_exist()
    {
        $this->setTestRoles(['test' => ['access cp', 'edit forms']]);
        $user = tap(User::make()->assignRole('test'))->save();
        $form = tap(Form::make('test'))->save();

        $this
            ->actingAs($user)
            ->postJson(cp_route('forms.connect.validate', [$form->handle(), 'unknown']), [])
            ->assertNotFound();
    }

    #[Test]
    public function it_denies_access_if_you_cant_edit_the_form()
    {
        $this->setTestRoles(['test' => ['access cp']]);
        $user = tap(User::make()->assignRole('test'))->save();
        $form = tap(Form::make('test'))->save();

        $this
            ->actingAs($user)
            ->postJson(cp_route('forms.connect.validate', [$form->handle(), 'email']), [['id' => 'abc', 'to' => ['new@example.com']]])
            ->assertForbidden();
    }
}

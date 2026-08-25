<?php

namespace Tests\Feature\Forms;

use PHPUnit\Framework\Attributes\Test;
use Statamic\Facades\Form;
use Statamic\Facades\User;
use Tests\FakesRoles;
use Tests\PreventSavingStacheItemsToDisk;
use Tests\TestCase;

class EditConnectionTest extends TestCase
{
    use FakesRoles;
    use PreventSavingStacheItemsToDisk;

    protected function resolveApplicationConfiguration($app)
    {
        parent::resolveApplicationConfiguration($app);

        $app['config']['statamic.forms.forms'] = $this->fakeStacheDirectory.'/forms';
    }

    #[Test]
    public function it_shows_the_edit_page()
    {
        $this->setTestRoles(['test' => ['access cp', 'edit forms']]);
        $user = tap(User::make()->assignRole('test'))->save();
        $form = tap(Form::make('test')->connections([
            'email' => [['id' => 'abc', 'to' => 'foo@example.com']],
        ]))->save();

        $this
            ->actingAs($user)
            ->get(cp_route('forms.connect.edit', [$form->handle(), 'email']))
            ->assertSuccessful()
            ->assertInertia(fn ($page) => $page
                ->component('forms/connect/Edit', false)
                ->where('connection.handle', 'email')
                ->where('connection.title', 'Email')
                ->where('component.name', 'email-connection')
                ->missing('component.props.action')
                ->missing('config')
                ->has('suggestableFields')
                ->where('action', cp_route('forms.connect.update', [$form->handle(), 'email']))
                ->where('value.0.id', 'abc')
                ->where('value.0.to', ['foo@example.com'])
                ->where('value.0.enabled', true)
                ->where('value.0.conditions', []));
    }

    #[Test]
    public function it_404s_if_the_connection_doesnt_exist()
    {
        $this->setTestRoles(['test' => ['access cp', 'edit forms']]);
        $user = tap(User::make()->assignRole('test'))->save();
        $form = tap(Form::make('test'))->save();

        $this
            ->actingAs($user)
            ->get(cp_route('forms.connect.edit', [$form->handle(), 'unknown']))
            ->assertNotFound();
    }

    #[Test]
    public function it_denies_access_if_you_dont_have_permission()
    {
        $this->setTestRoles(['test' => ['access cp']]);
        $user = tap(User::make()->assignRole('test'))->save();
        $form = tap(Form::make('test'))->save();

        $this
            ->from('/original')
            ->actingAs($user)
            ->get(cp_route('forms.connect.edit', [$form->handle(), 'email']))
            ->assertRedirect('/original')
            ->assertSessionHas('error');
    }
}

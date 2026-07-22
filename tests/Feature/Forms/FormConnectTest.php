<?php

namespace Tests\Feature\Forms;

use PHPUnit\Framework\Attributes\Test;
use Statamic\Facades\Form;
use Statamic\Facades\User;
use Statamic\Forms\Connections\Connection;
use Statamic\Support\VueComponent;
use Tests\FakesRoles;
use Tests\PreventSavingStacheItemsToDisk;
use Tests\TestCase;

class FormConnectTest extends TestCase
{
    use FakesRoles;
    use PreventSavingStacheItemsToDisk;

    protected function resolveApplicationConfiguration($app)
    {
        parent::resolveApplicationConfiguration($app);

        $app['config']['statamic.forms.forms'] = $this->fakeStacheDirectory.'/forms';

        $app->booting(fn () => AcmeConnection::register());
    }

    #[Test]
    public function it_shows_the_page_with_the_edit_forms_permission()
    {
        $this->setTestRoles(['test' => ['access cp', 'edit forms']]);
        $user = tap(User::make()->assignRole('test'))->save();
        $form = tap(Form::make('test'))->save();

        $this
            ->actingAs($user)
            ->get(cp_route('forms.connect.index', $form->handle()))
            ->assertSuccessful()
            ->assertInertia(fn ($page) => $page->component('forms/connect/Index'));
    }

    #[Test]
    public function it_shows_the_page_with_the_per_form_edit_permission()
    {
        $this->setTestRoles(['test' => ['access cp', 'edit test form']]);
        $user = tap(User::make()->assignRole('test'))->save();
        $form = tap(Form::make('test'))->save();

        $this
            ->actingAs($user)
            ->get(cp_route('forms.connect.index', $form->handle()))
            ->assertSuccessful()
            ->assertInertia(fn ($page) => $page->component('forms/connect/Index'));
    }

    #[Test]
    public function it_shows_the_page_with_the_configure_forms_permission()
    {
        $this->setTestRoles(['test' => ['access cp', 'configure forms']]);
        $user = tap(User::make()->assignRole('test'))->save();
        $form = tap(Form::make('test'))->save();

        $this
            ->actingAs($user)
            ->get(cp_route('forms.connect.index', $form->handle()))
            ->assertSuccessful()
            ->assertInertia(fn ($page) => $page->component('forms/connect/Index'));
    }

    #[Test]
    public function it_lists_the_registered_connection_types()
    {
        $this->setTestRoles(['test' => ['access cp', 'edit forms']]);
        $user = tap(User::make()->assignRole('test'))->save();
        $form = tap(Form::make('test')->connections([
            'email' => [['id' => 'abc', 'to' => 'foo@example.com']],
        ]))->save();

        $this
            ->actingAs($user)
            ->get(cp_route('forms.connect.index', $form->handle()))
            ->assertSuccessful()
            ->assertInertia(fn ($page) => $page
                ->component('forms/connect/Index')
                ->has('connections', 3)
                ->where('connections.0.handle', 'email')
                ->where('connections.0.title', 'Email')
                ->where('connections.0.count', 1)
                ->where('connections.0.url', cp_route('forms.connect.show', [$form->handle(), 'email']))
                ->where('connections.1.handle', 'webhook')
                ->where('connections.1.count', 0)
                ->where('connections.2.handle', 'acme'));
    }

    #[Test]
    public function it_denies_access_with_only_the_view_form_submissions_permission()
    {
        $this->setTestRoles(['test' => ['access cp', 'view form submissions']]);
        $user = tap(User::make()->assignRole('test'))->save();
        $form = tap(Form::make('test'))->save();

        $this
            ->from('/original')
            ->actingAs($user)
            ->get(cp_route('forms.connect.index', $form->handle()))
            ->assertRedirect('/original')
            ->assertSessionHas('error');
    }

    #[Test]
    public function it_denies_access_with_only_the_per_form_view_submissions_permission()
    {
        $this->setTestRoles(['test' => ['access cp', 'view test form submissions']]);
        $user = tap(User::make()->assignRole('test'))->save();
        $form = tap(Form::make('test'))->save();

        $this
            ->from('/original')
            ->actingAs($user)
            ->get(cp_route('forms.connect.index', $form->handle()))
            ->assertRedirect('/original')
            ->assertSessionHas('error');
    }

    #[Test]
    public function it_denies_access_with_no_permissions()
    {
        $this->setTestRoles(['test' => ['access cp']]);
        $user = tap(User::make()->assignRole('test'))->save();
        $form = tap(Form::make('test'))->save();

        $this
            ->from('/original')
            ->actingAs($user)
            ->get(cp_route('forms.connect.index', $form->handle()))
            ->assertRedirect('/original')
            ->assertSessionHas('error');
    }

    #[Test]
    public function it_shows_a_connection_type()
    {
        $this->setTestRoles(['test' => ['access cp', 'edit forms']]);
        $user = tap(User::make()->assignRole('test'))->save();
        $form = tap(Form::make('test')->connections([
            'email' => [['id' => 'abc', 'to' => 'foo@example.com']],
        ]))->save();

        $this
            ->actingAs($user)
            ->get(cp_route('forms.connect.show', [$form->handle(), 'email']))
            ->assertSuccessful()
            ->assertInertia(fn ($page) => $page
                ->component('forms/connect/Show', false)
                ->where('connection.handle', 'email')
                ->where('connection.title', 'Email')
                ->where('component.name', 'email-connection')
                ->where('component.props.action', cp_route('forms.connect.email.update', $form->handle()))
                ->has('suggestableFields')
                ->where('config', [['id' => 'abc', 'to' => 'foo@example.com', '_id' => 'abc']]));
    }

    #[Test]
    public function it_returns_404_for_an_unknown_connection_type()
    {
        $this->setTestRoles(['test' => ['access cp', 'edit forms']]);
        $user = tap(User::make()->assignRole('test'))->save();
        $form = tap(Form::make('test'))->save();

        $this
            ->actingAs($user)
            ->get(cp_route('forms.connect.show', [$form->handle(), 'unknown']))
            ->assertNotFound();
    }

    #[Test]
    public function it_denies_access_to_a_connection_type_without_permission()
    {
        $this->setTestRoles(['test' => ['access cp']]);
        $user = tap(User::make()->assignRole('test'))->save();
        $form = tap(Form::make('test'))->save();

        $this
            ->from('/original')
            ->actingAs($user)
            ->get(cp_route('forms.connect.show', [$form->handle(), 'email']))
            ->assertRedirect('/original')
            ->assertSessionHas('error');
    }

    #[Test]
    public function custom_connection_routes_are_reachable_with_the_edit_permission()
    {
        $this->setTestRoles(['test' => ['access cp', 'edit forms']]);
        $user = tap(User::make()->assignRole('test'))->save();
        $form = tap(Form::make('test'))->save();

        $this
            ->actingAs($user)
            ->postJson(cp_route('forms.connect.acme.process', $form->handle()))
            ->assertOk()
            ->assertExactJson(['processed' => true]);
    }

    #[Test]
    public function custom_connection_routes_are_denied_without_the_edit_permission()
    {
        $this->setTestRoles(['test' => ['access cp']]);
        $user = tap(User::make()->assignRole('test'))->save();
        $form = tap(Form::make('test'))->save();

        $this
            ->actingAs($user)
            ->postJson(cp_route('forms.connect.acme.process', $form->handle()))
            ->assertForbidden();
    }
}

class AcmeConnection extends Connection
{
    public function render($form): VueComponent
    {
        return VueComponent::render('acme-connection');
    }

    public function routes($router): void
    {
        $router->post('process', fn () => ['processed' => true])->name('process');
    }
}

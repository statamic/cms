<?php

namespace Tests\Feature\Forms;

use PHPUnit\Framework\Attributes\Test;
use Statamic\Facades\Form;
use Statamic\Facades\User;
use Statamic\Forms\Connectors\Connector;
use Statamic\Support\VueComponent;
use Tests\FakesRoles;
use Tests\PreventSavingStacheItemsToDisk;
use Tests\TestCase;

class ViewConnectorsTest extends TestCase
{
    use FakesRoles;
    use PreventSavingStacheItemsToDisk;

    protected function resolveApplicationConfiguration($app)
    {
        parent::resolveApplicationConfiguration($app);

        $app['config']['statamic.forms.forms'] = $this->fakeStacheDirectory.'/forms';

        $app->booting(fn () => AcmeConnector::register());
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
    public function it_lists_connectors()
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
                ->has('connectors', 3)
                ->where('connectors.0.handle', 'email')
                ->where('connectors.0.title', 'Email')
                ->where('connectors.0.count', 1)
                ->where('connectors.0.url', cp_route('forms.connect.edit', [$form->handle(), 'email']))
                ->where('connectors.1.handle', 'webhook')
                ->where('connectors.1.count', 0)
                ->where('connectors.2.handle', 'acme'));
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
}

class AcmeConnector extends Connector
{
    public function render($form): VueComponent
    {
        return VueComponent::render('acme-connector');
    }
}

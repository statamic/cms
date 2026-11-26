<?php

namespace Tests\Feature\Forms;

use Facades\Statamic\Console\Processes\Composer;
use Illuminate\Routing\Router;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Contracts\Forms\Submission;
use Statamic\Facades\Blueprint as BlueprintFacade;
use Statamic\Facades\Form;
use Statamic\Facades\User;
use Statamic\Fields\Blueprint;
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

    public function setUp(): void
    {
        parent::setUp();

        Composer::shouldReceive('isInstalled')->with('statamic/forms-pro')->andReturn(false)->byDefault();
    }

    #[Test]
    #[DataProvider('connectionPermissionsProvider')]
    public function it_shows_the_page_with_edit_and_view_submissions_permissions(array $permissions)
    {
        $this->setTestRoles(['test' => ['access cp', ...$permissions]]);
        $user = tap(User::make()->assignRole('test'))->save();
        $form = tap(Form::make('test'))->save();

        $this
            ->actingAs($user)
            ->get(cp_route('forms.connect.index', $form->handle()))
            ->assertSuccessful()
            ->assertInertia(fn ($page) => $page
                ->component('forms/connect/Index')
                ->where('can.editConnections', true));
    }

    public static function connectionPermissionsProvider(): array
    {
        return [
            'global' => [['edit forms', 'view form submissions']],
            'per form' => [['edit test form', 'view test form submissions']],
            'global edit, per form submissions' => [['edit forms', 'view test form submissions']],
        ];
    }

    #[Test]
    #[DataProvider('editOnlyPermissionsProvider')]
    public function it_denies_access_with_only_edit_permissions(array $permissions)
    {
        $this->setTestRoles(['test' => ['access cp', ...$permissions]]);
        $user = tap(User::make()->assignRole('test'))->save();
        $form = tap(Form::make('test'))->save();

        $this
            ->from('/original')
            ->actingAs($user)
            ->get(cp_route('forms.connect.index', $form->handle()))
            ->assertRedirect('/original')
            ->assertSessionHas('error');
    }

    public static function editOnlyPermissionsProvider(): array
    {
        return [
            'edit forms' => [['edit forms']],
            'edit test form' => [['edit test form']],
        ];
    }

    #[Test]
    #[DataProvider('editOnlyPermissionsProvider')]
    public function it_denies_addon_connector_routes_with_only_edit_permissions(array $permissions)
    {
        $this->setTestRoles(['test' => ['access cp', ...$permissions]]);
        $user = tap(User::make()->assignRole('test'))->save();
        $form = tap(Form::make('test'))->save();

        $this
            ->actingAs($user)
            ->postJson(cp_route('forms.connect.acme.process', $form->handle()))
            ->assertForbidden();
    }

    #[Test]
    public function it_allows_addon_connector_routes_with_edit_and_view_submissions_permissions()
    {
        $this->setTestRoles(['test' => ['access cp', 'edit test form', 'view test form submissions']]);
        $user = tap(User::make()->assignRole('test'))->save();
        $form = tap(Form::make('test'))->save();

        $this
            ->actingAs($user)
            ->postJson(cp_route('forms.connect.acme.process', $form->handle()))
            ->assertOk()
            ->assertSee('processed');
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
        $this->setTestRoles(['test' => ['access cp', 'edit forms', 'view form submissions']]);
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
    public function render(): VueComponent
    {
        return VueComponent::render('acme-connector');
    }

    public function blueprint(): Blueprint
    {
        return BlueprintFacade::make();
    }

    protected function job(Submission $submission, array $connection): ?object
    {
        return null;
    }

    public function routes(Router $router): void
    {
        $router->post('/', fn () => 'processed')->name('process');
    }
}

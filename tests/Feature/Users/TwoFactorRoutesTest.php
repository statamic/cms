<?php

namespace Tests\Feature\Users;

use Illuminate\Support\Facades\Route;
use Orchestra\Testbench\Attributes\DefineEnvironment;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Facades\User;
use Tests\PreventSavingStacheItemsToDisk;
use Tests\TestCase;

#[Group('2fa')]
class TwoFactorRoutesTest extends TestCase
{
    use PreventSavingStacheItemsToDisk;

    protected function resolveApplicationConfiguration($app)
    {
        parent::resolveApplicationConfiguration($app);

        $app->booted(function () {
            Route::get('/test-frontend-route', function () {
                return 'ok';
            })->middleware('statamic.web');

            Route::get('/custom-setup', function () {
                return 'setup page';
            })->middleware('statamic.web');

            Route::get('/login', fn () => 'login page')->name('login');
        });
    }

    #[Test]
    #[DefineEnvironment('disableTwoFactor')]
    public function two_factor_routes_are_not_registered_when_two_factor_is_disabled()
    {
        $this->assertFalse(Route::has('statamic.cp.two-factor-challenge'));
        $this->assertFalse(Route::has('statamic.cp.two-factor-setup'));
        $this->assertFalse(Route::has('statamic.cp.users.two-factor.enable'));
        $this->assertFalse(Route::has('statamic.cp.users.two-factor.confirm'));
        $this->assertFalse(Route::has('statamic.cp.users.two-factor.disable'));
        $this->assertFalse(Route::has('statamic.cp.users.two-factor.recovery-codes.show'));
        $this->assertFalse(Route::has('statamic.cp.users.two-factor.recovery-codes.generate'));
        $this->assertFalse(Route::has('statamic.cp.users.two-factor.recovery-codes.download'));
        $this->assertFalse(Route::has('statamic.two-factor-challenge'));
        $this->assertFalse(Route::has('statamic.two-factor-setup'));
        $this->assertFalse(Route::has('statamic.users.two-factor.enable'));
        $this->assertFalse(Route::has('statamic.users.two-factor.confirm'));
        $this->assertFalse(Route::has('statamic.users.two-factor.recovery-codes.show'));
        $this->assertFalse(Route::has('statamic.users.two-factor.recovery-codes.generate'));
        $this->assertFalse(Route::has('statamic.users.two-factor.recovery-codes.download'));
    }

    #[Test]
    public function cp_two_factor_setup_middleware_redirects_when_two_factor_is_enforced()
    {
        config()->set('statamic.users.two_factor_enforced_roles', ['*']);

        $user = tap(User::make()->makeSuper()->email('admin@domain.com'))->save();

        $this
            ->actingAs($user)
            ->get(cp_route('dashboard'))
            ->assertRedirect(cp_route('two-factor-setup'))
            ->assertSessionHas('url.intended', cp_route('dashboard'));
    }

    #[Test]
    #[DefineEnvironment('disableTwoFactor')]
    public function cp_two_factor_setup_middleware_does_not_redirect_when_two_factor_is_disabled()
    {
        config()->set('statamic.users.two_factor_enforced_roles', ['*']);

        $user = tap(User::make()->makeSuper()->email('admin@domain.com'))->save();

        $this
            ->actingAs($user)
            ->get(cp_route('dashboard'))
            ->assertOk();
    }

    #[Test]
    public function cp_two_factor_setup_middleware_ignores_frontend_setup_url_config()
    {
        config([
            'statamic.users.two_factor_enforced_roles' => ['*'],
            'statamic.users.two_factor_setup_url' => '/custom-setup',
        ]);

        $user = tap(User::make()->makeSuper()->email('admin@domain.com'))->save();

        $this
            ->actingAs($user)
            ->get(cp_route('dashboard'))
            ->assertRedirect(cp_route('two-factor-setup'))
            ->assertSessionHas('url.intended', cp_route('dashboard'));
    }

    #[Test]
    public function frontend_two_factor_setup_middleware_redirects_when_two_factor_is_enforced()
    {
        config()->set('statamic.users.two_factor_enforced_roles', ['*']);

        $user = tap(User::make()->makeSuper()->email('admin@domain.com'))->save();

        $this
            ->actingAs($user)
            ->get('/test-frontend-route')
            ->assertRedirect(route('statamic.two-factor-setup'))
            ->assertSessionHas('url.intended', url('/test-frontend-route'));
    }

    #[Test]
    public function frontend_two_factor_setup_middleware_generates_secret_when_none_exists()
    {
        config()->set('statamic.users.two_factor_enforced_roles', ['*']);

        $user = tap(User::make()->makeSuper()->email('admin@domain.com'))->save();

        $this->assertNull($user->two_factor_secret);

        $this
            ->actingAs($user)
            ->get('/test-frontend-route')
            ->assertRedirect(route('statamic.two-factor-setup'))
            ->assertSessionHas('url.intended', url('/test-frontend-route'));

        $this->assertNotNull($user->fresh()->two_factor_secret);
    }

    #[Test]
    public function frontend_two_factor_setup_middleware_does_not_regenerate_existing_secret()
    {
        config()->set('statamic.users.two_factor_enforced_roles', ['*']);

        $user = tap(User::make()->makeSuper()->email('admin@domain.com')->data([
            'two_factor_secret' => $existing = encrypt(app(\Statamic\Contracts\Auth\TwoFactor\TwoFactorAuthenticationProvider::class)->generateSecretKey()),
        ]))->save();

        $this
            ->actingAs($user)
            ->get('/test-frontend-route')
            ->assertRedirect(route('statamic.two-factor-setup'))
            ->assertSessionHas('url.intended', url('/test-frontend-route'));

        $this->assertEquals($existing, $user->fresh()->two_factor_secret);
    }

    #[Test]
    public function frontend_two_factor_setup_middleware_redirects_to_configured_url()
    {
        config([
            'statamic.users.two_factor_enforced_roles' => ['*'],
            'statamic.users.two_factor_setup_url' => '/custom-setup',
        ]);

        $user = tap(User::make()->makeSuper()->email('admin@domain.com'))->save();

        $this
            ->actingAs($user)
            ->get('/test-frontend-route')
            ->assertRedirect('/custom-setup');
    }

    #[Test]
    public function frontend_two_factor_setup_middleware_allows_configured_url_through()
    {
        config([
            'statamic.users.two_factor_enforced_roles' => ['*'],
            'statamic.users.two_factor_setup_url' => '/custom-setup',
        ]);

        $user = tap(User::make()->makeSuper()->email('admin@domain.com'))->save();

        $this
            ->actingAs($user)
            ->get('/custom-setup')
            ->assertOk()
            ->assertSee('setup page');
    }

    #[Test]
    public function frontend_two_factor_action_routes_require_authentication()
    {
        $routes = [
            ['post', route('statamic.users.two-factor.enable')],
            ['post', route('statamic.users.two-factor.confirm')],
            ['delete', route('statamic.users.two-factor.disable')],
            ['get', route('statamic.users.two-factor.recovery-codes.show')],
            ['post', route('statamic.users.two-factor.recovery-codes.generate')],
            ['get', route('statamic.users.two-factor.recovery-codes.download')],
        ];

        foreach ($routes as [$method, $url]) {
            $this->{$method}($url)->assertRedirect('/login');
        }
    }

    #[Test]
    #[DefineEnvironment('disableTwoFactor')]
    public function frontend_two_factor_setup_middleware_does_not_redirect_when_two_factor_is_disabled()
    {
        config()->set('statamic.users.two_factor_enforced_roles', ['*']);

        $user = tap(User::make()->makeSuper()->email('admin@domain.com'))->save();

        $this
            ->actingAs($user)
            ->get('/test-frontend-route')
            ->assertOk();
    }

    protected function disableTwoFactor($app)
    {
        $app['config']->set('statamic.users.two_factor_enabled', false);
    }
}

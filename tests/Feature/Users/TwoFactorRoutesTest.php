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
            ->assertRedirect(cp_route('two-factor-setup', ['referer' => cp_route('dashboard')]));
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
    public function frontend_two_factor_setup_middleware_redirects_when_two_factor_is_enforced()
    {
        config()->set('statamic.users.two_factor_enforced_roles', ['*']);

        $user = tap(User::make()->makeSuper()->email('admin@domain.com'))->save();

        $this
            ->actingAs($user)
            ->get('/test-frontend-route')
            ->assertRedirect(route('statamic.two-factor-setup', ['referer' => url('/test-frontend-route')]));
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

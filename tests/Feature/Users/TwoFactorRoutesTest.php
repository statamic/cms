<?php

namespace Tests\Feature\Users;

use Illuminate\Support\Facades\Route;
use Orchestra\Testbench\Attributes\DefineEnvironment;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

#[Group('2fa')]
class TwoFactorRoutesTest extends TestCase
{
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
    }

    protected function disableTwoFactor($app)
    {
        $app['config']->set('statamic.users.two_factor_enabled', false);
    }
}

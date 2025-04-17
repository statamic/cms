<?php

namespace Tests\Http\Middleware;

use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Auth\TwoFactor\TwoFactorAuthenticationProvider;
use Statamic\Auth\TwoFactor\RecoveryCode;
use Statamic\Facades\User;
use Statamic\Http\Middleware\CP\SetupAvailableWhenTwoFactorSetupIncomplete;
use Tests\PreventSavingStacheItemsToDisk;
use Tests\TestCase;

class SetupAvailableWhenTwoFactorSetupIncompleteTest extends TestCase
{
    use PreventSavingStacheItemsToDisk;

    #[Test]
    public function it_allows_access_to_setup_route_when_two_factor_is_not_setup()
    {
        $request = Request::create(cp_route('two-factor.setup'));
        $request->setUserResolver(fn () => $this->user());

        $response = (new SetupAvailableWhenTwoFactorSetupIncomplete)->handle($request, fn () => null);

        $this->assertNull($response);
    }

    #[Test]
    public function it_does_not_allow_access_when_two_factor_is_setup()
    {
        $request = Request::create(cp_route('two-factor.setup'));
        $request->setUserResolver(fn () => $this->userWithTwoFactorEnabled());

        $response = (new SetupAvailableWhenTwoFactorSetupIncomplete)->handle($request, fn () => null);

        $this->assertTrue($response->isRedirect(cp_route('index')));
    }

    private function user()
    {
        return tap(User::make()->makeSuper())->save();
    }

    private function userWithTwoFactorEnabled()
    {
        $user = $this->user();

        $user->merge([
            'two_factor_confirmed_at' => now()->timestamp,
            'two_factor_completed' => now()->timestamp,
            'two_factor_secret' => encrypt(app(TwoFactorAuthenticationProvider::class)->generateSecretKey()),
            'two_factor_recovery_codes' => encrypt(json_encode(Collection::times(8, function () {
                return RecoveryCode::generate();
            })->all())),
        ]);

        $user->save();

        return $user;
    }
}

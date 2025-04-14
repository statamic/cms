<?php

namespace Tests\Auth\TwoFactor;

use Illuminate\Support\Collection;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Auth\TwoFactor\Google2FA;
use Statamic\Auth\TwoFactor\RecoveryCode;
use Statamic\Facades\User;
use Tests\PreventSavingStacheItemsToDisk;
use Tests\TestCase;

class LoginControllerTest extends TestCase
{
    use PreventSavingStacheItemsToDisk;

    #[Test]
    public function it_allows_logging_in()
    {
        $user = $this->userWithTwoFactorEnabled();

        $this
            ->actingAs($user)
            ->post(cp_route('login'), [
                'email' => $user->email(),
                'password' => 'secret',
            ])
            ->assertRedirect(cp_route('index'));
    }

    #[Test]
    public function it_clears_the_last_challenged_variable_when_logged_out()
    {
        $this->actingAs($user = $this->userWithTwoFactorEnabled());

        $user->setLastTwoFactorChallenged();

        $this->assertNotNull($user->getLastTwoFactorChallenged());

        $this->get(route('statamic.logout'))->assertRedirect();

        $this->assertNull($user->getLastTwoFactorChallenged());
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
            'two_factor_secret' => encrypt(app(Google2FA::class)->generateSecretKey()),
            'two_factor_recovery_codes' => encrypt(json_encode(Collection::times(8, function () {
                return RecoveryCode::generate();
            })->all())),
        ]);

        $user->save();

        return $user;
    }
}

<?php

namespace Tests\Auth\TwoFactor;

use Illuminate\Support\Collection;
use PHPUnit\Framework\Attributes\Test;
use PragmaRX\Google2FA\Google2FA;
use Statamic\Auth\TwoFactor\TwoFactorAuthenticationProvider;
use Statamic\Auth\TwoFactor\RecoveryCode;
use Statamic\Facades\User;
use Tests\PreventSavingStacheItemsToDisk;
use Tests\TestCase;

class TwoFactorSetupControllerTest extends TestCase
{
    use PreventSavingStacheItemsToDisk;

    #[Test]
    public function it_shows_the_two_factor_setup_view()
    {
        $this
            ->actingAs($this->user())
            ->get(cp_route('two-factor.setup'))
            ->assertViewIs('statamic::auth.two-factor.setup');
    }

    #[Test]
    public function it_redirects_to_the_dashboard_if_the_user_is_already_set_up()
    {
        $this
            ->actingAs($this->userWithTwoFactorEnabled())
            ->get(cp_route('two-factor.setup'))
            ->assertRedirect(cp_route('index'));
    }

    #[Test]
    public function it_shows_the_recovery_codes()
    {
        $user = $this->user();

        $user->set('two_factor_secret', encrypt(app(TwoFactorAuthenticationProvider::class)->generateSecretKey()));
        $user->set('two_factor_recovery_codes', encrypt(json_encode(Collection::times(8, function () {
            return RecoveryCode::generate();
        })->all())));

        $this
            ->actingAs($user)
            ->post(cp_route('two-factor.confirm'), [
                'code' => $this->getOneTimeCode($user),
            ])
            ->assertViewIs('statamic::auth.two-factor.recovery-codes')
            ->assertViewHas('recovery_codes');
    }

    #[Test]
    public function it_completes_setup_and_redirects()
    {
        $this
            ->actingAs($user = $this->userWithTwoFactorEnabled())
            ->post(cp_route('two-factor.complete'), [
                'code' => $this->getOneTimeCode($user),
            ])
            ->assertRedirect(cp_route('index'));
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

    private function getOneTimeCode($user): string
    {
        $internalProvider = app(Google2FA::class);

        return $internalProvider->getCurrentOtp($user->twoFactorSecretKey());
    }
}

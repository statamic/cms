<?php

namespace Tests\Auth\TwoFactor;

use Illuminate\Support\Collection;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Auth\TwoFactor\Google2FA;
use Statamic\Auth\TwoFactor\RecoveryCode;
use Statamic\Facades\User;
use Tests\PreventSavingStacheItemsToDisk;
use Tests\TestCase;

class TwoFactorChallengeControllerTest extends TestCase
{
    use PreventSavingStacheItemsToDisk;

    #[Test]
    public function it_shows_the_two_factor_challenge_view()
    {
        $this
            ->actingAs($this->userWithTwoFactorEnabled())
            ->get(cp_route('two-factor.challenge'))
            ->assertViewIs('statamic::auth.two-factor.challenge');
    }

    #[Test]
    public function it_sets_a_referrer_url_when_one_is_present_and_not_a_two_factor_route()
    {
        $this->assertNull(session()->get('statamic_two_factor_referrer'));

        $this
            ->actingAs($this->userWithTwoFactorEnabled())
            ->get(cp_route('two-factor.challenge'), [
                'referer' => cp_route('collections.index'),
            ]);

        $this->assertNotNull(session()->get('statamic_two_factor_referrer'));
        $this->assertEquals(cp_route('collections.index'), session()->get('statamic_two_factor_referrer'));
    }

    #[Test]
    public function it_redirects_to_the_cp_after_a_successful_challenge()
    {
        $this
            ->actingAs($this->userWithTwoFactorEnabled())
            ->post(cp_route('two-factor.challenge'), [
                'code' => $this->getOneTimeCode(),
            ])
            ->assertRedirect(cp_route('index'));
    }

    #[Test]
    public function it_redirects_to_the_referrer_after_a_successful_challenge()
    {
        $user = $this->userWithTwoFactorEnabled();

        // Set the referrer
        $this
            ->actingAs($user)
            ->get(cp_route('two-factor.challenge'), [
                'referer' => cp_route('collections.index'),
            ]);

        // Ensure the POST request redirects to the original referrer
        $this
            ->actingAs($user)
            ->post(cp_route('two-factor.challenge'), [
                'code' => $this->getOneTimeCode(),
            ])
            ->assertRedirect(cp_route('collections.index'));
    }

    private function user()
    {
        return tap(User::make()->makeSuper())->save();
    }

    private function userWithTwoFactorEnabled()
    {
        $user = $this->user();

        $user->merge([
            'two_factor_confirmed_at' => now(),
            'two_factor_completed' => now(),
            'two_factor_secret' => encrypt(app(Google2FA::class)->generateSecretKey()),
            'two_factor_recovery_codes' => encrypt(json_encode(Collection::times(8, function () {
                return RecoveryCode::generate();
            })->all())),
        ]);

        $user->save();

        return $user;
    }

    private function getOneTimeCode()
    {
        $provider = app(Google2FA::class);

        // get a one-time code (so we can make sure we have a wrong one in the test)
        $internalProvider = app(\PragmaRX\Google2FA\Google2FA::class);

        return $internalProvider->getCurrentOtp($provider->getSecretKey());
    }
}

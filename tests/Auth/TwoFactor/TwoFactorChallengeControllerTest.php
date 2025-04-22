<?php

namespace Tests\Auth\TwoFactor;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Notification;
use PHPUnit\Framework\Attributes\Test;
use PragmaRX\Google2FA\Google2FA;
use Statamic\Auth\TwoFactor\RecoveryCode;
use Statamic\Auth\TwoFactor\TwoFactorAuthenticationProvider;
use Statamic\Facades\User;
use Statamic\Notifications\RecoveryCodeUsed;
use Tests\PreventSavingStacheItemsToDisk;
use Tests\TestCase;

class TwoFactorChallengeControllerTest extends TestCase
{
    use PreventSavingStacheItemsToDisk;

    #[Test]
    public function it_shows_the_two_factor_challenge_page()
    {
        $user = $this->userWithTwoFactorEnabled();

        $this
            ->session(['login.id' => $user->id()])
            ->get(cp_route('two-factor-challenge'))
            ->assertViewIs('statamic::auth.two-factor.challenge');
    }

    #[Test]
    public function it_doesnt_show_the_two_factor_challenge_page_when_authenticated()
    {
        $user = $this->userWithTwoFactorEnabled();

        $this
            ->actingAs($user)
            ->get(cp_route('two-factor-challenge'))
            ->assertRedirect();
    }

    #[Test]
    public function it_doesnt_show_the_two_factor_challenge_page_without_challenged_user_in_the_session()
    {
        $this
            ->get(cp_route('two-factor-challenge'))
            ->assertRedirect(cp_route('login'));
    }

    #[Test]
    public function it_can_complete_challenge_with_one_time_code()
    {
        $user = $this->userWithTwoFactorEnabled();

        $this
            ->session(['login.id' => $user->id()])
            ->post(cp_route('two-factor-challenge'), [
                'code' => $this->getOneTimeCode($user),
            ])
            ->assertRedirect(cp_route('index'));

        $this->assertAuthenticatedAs($user);
    }

    #[Test]
    public function it_cant_complete_challenge_with_invalid_one_time_code()
    {
        $user = $this->userWithTwoFactorEnabled();

        $this
            ->session(['login.id' => $user->id()])
            ->post(cp_route('two-factor-challenge'), [
                'code' => '123456',
            ])
            ->assertRedirect(cp_route('two-factor-challenge'))
            ->assertSessionHasErrors('code');

        $this->assertGuest();
    }

    #[Test]
    public function it_can_complete_challenge_with_recovery_code()
    {
        Notification::fake();

        $user = $this->userWithTwoFactorEnabled();
        $recoveryCode = collect($user->recoveryCodes())->first();

        $this
            ->session(['login.id' => $user->id()])
            ->post(cp_route('two-factor-challenge'), [
                'recovery_code' => $recoveryCode,
            ])
            ->assertRedirect(cp_route('index'));

        $this->assertAuthenticatedAs($user);

        $this->assertNotContains($recoveryCode, $user->fresh()->recoveryCodes());

        Notification::assertSentTo($user, RecoveryCodeUsed::class);
    }

    #[Test]
    public function it_cant_complete_challenge_with_invalid_recovery_code()
    {
        Notification::fake();

        $user = $this->userWithTwoFactorEnabled();
        $originalRecoveryCodes = $user->recoveryCodes();

        $this
            ->session(['login.id' => $user->id()])
            ->post(cp_route('two-factor-challenge'), [
                'recovery_code' => 'abcdefg',
            ])
            ->assertRedirect(cp_route('two-factor-challenge'))
            ->assertSessionHasErrors('recovery_code');

        $this->assertGuest();

        $this->assertEquals($originalRecoveryCodes, $user->fresh()->recoveryCodes());

        Notification::assertNotSentTo($user, RecoveryCodeUsed::class);
    }

    #[Test]
    public function it_redirects_to_referer_url_after_successful_challenge()
    {
        $user = $this->userWithTwoFactorEnabled();

        $this
            ->session(['login.id' => $user->id()])
            ->post(cp_route('two-factor-challenge'), [
                'code' => $this->getOneTimeCode($user),
                'referer' => 'http://localhost/cp/collections',
            ])
            ->assertRedirect('http://localhost/cp/collections');

        $this->assertAuthenticatedAs($user);
    }

    #[Test]
    public function it_redirects_to_intended_url_after_successful_challenge()
    {
        $user = $this->userWithTwoFactorEnabled();

        $this
            ->session([
                'login.id' => $user->id(),
                'url.intended' => 'http://localhost/cp/collections',
            ])
            ->post(cp_route('two-factor-challenge'), [
                'code' => $this->getOneTimeCode($user),
            ])
            ->assertRedirect('http://localhost/cp/collections');

        $this->assertAuthenticatedAs($user);
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

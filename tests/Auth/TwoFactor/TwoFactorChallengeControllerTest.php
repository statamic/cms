<?php

namespace Tests\Auth\TwoFactor;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Route;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use PragmaRX\Google2FA\Google2FA;
use Statamic\Auth\TwoFactor\RecoveryCode;
use Statamic\Auth\TwoFactor\TwoFactorAuthenticationProvider;
use Statamic\Events\TwoFactorAuthenticationFailed;
use Statamic\Events\TwoFactorRecoveryCodeReplaced;
use Statamic\Events\ValidTwoFactorAuthenticationCodeProvided;
use Statamic\Facades\User;
use Statamic\Http\Middleware\CP\RequireElevatedSession;
use Statamic\Notifications\RecoveryCodeUsed;
use Tests\PreventSavingStacheItemsToDisk;
use Tests\TestCase;

#[Group('elevated-sessions')]
class TwoFactorChallengeControllerTest extends TestCase
{
    use PreventSavingStacheItemsToDisk;

    protected function resolveApplicationConfiguration($app)
    {
        parent::resolveApplicationConfiguration($app);

        $app->booted(function () {
            Route::get('/requires-elevated-session', function () {
                return 'ok';
            })->middleware(RequireElevatedSession::class);
        });
    }

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
        Event::fake();

        $user = $this->userWithTwoFactorEnabled();

        $this
            ->session(['login.id' => $user->id()])
            ->post(cp_route('two-factor-challenge'), [
                'code' => $this->getOneTimeCode($user),
            ])
            ->assertRedirect(cp_route('index'));

        $this->assertAuthenticatedAs($user);

        Event::assertDispatched(ValidTwoFactorAuthenticationCodeProvided::class, function (ValidTwoFactorAuthenticationCodeProvided $event) use ($user) {
            return $event->user->id() === $user->id();
        });
    }

    #[Test]
    public function it_cant_complete_challenge_with_invalid_one_time_code()
    {
        Event::fake();

        $user = $this->userWithTwoFactorEnabled();

        $this
            ->session(['login.id' => $user->id()])
            ->post(cp_route('two-factor-challenge'), [
                'code' => '123456',
            ])
            ->assertRedirect(cp_route('two-factor-challenge'))
            ->assertSessionHasErrors('code');

        $this->assertGuest();

        Event::assertDispatched(TwoFactorAuthenticationFailed::class, function (TwoFactorAuthenticationFailed $event) use ($user) {
            return $event->user->id() === $user->id();
        });
    }

    #[Test]
    public function it_can_complete_challenge_with_recovery_code()
    {
        Event::fake();
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

        Event::assertDispatched(TwoFactorRecoveryCodeReplaced::class, function (TwoFactorRecoveryCodeReplaced $event) use ($user, $recoveryCode) {
            return $event->user->id() === $user->id() && $event->code === $recoveryCode;
        });

        Notification::assertSentTo($user, RecoveryCodeUsed::class);
    }

    #[Test]
    public function it_cant_complete_challenge_with_invalid_recovery_code()
    {
        Event::fake();
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

        Event::assertNotDispatched(TwoFactorRecoveryCodeReplaced::class, function (TwoFactorRecoveryCodeReplaced $event) use ($user) {
            return $event->user->id() === $user->id() && $event->code === 'abcdefg';
        });

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

    #[Test]
    public function the_session_is_elevated_upon_login()
    {
        $user = $this->userWithTwoFactorEnabled();

        $this
            ->session([
                'login.id' => $user->id(),
                'url.intended' => 'http://localhost/requires-elevated-session',
            ])
            ->post(cp_route('two-factor-challenge'), [
                'code' => $this->getOneTimeCode($user),
            ]);

        $this
            ->get('/requires-elevated-session')
            ->assertOk();
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

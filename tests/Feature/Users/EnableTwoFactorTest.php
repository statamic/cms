<?php

namespace Feature\Users;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Event;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use PragmaRX\Google2FA\Google2FA;
use Statamic\Auth\TwoFactor\RecoveryCode;
use Statamic\Contracts\Auth\TwoFactor\TwoFactorAuthenticationProvider;
use Statamic\Events\TwoFactorAuthenticationEnabled;
use Statamic\Facades\User;
use Tests\PreventSavingStacheItemsToDisk;
use Tests\TestCase;

#[Group('elevated-session')]
#[Group('2fa')]
class EnableTwoFactorTest extends TestCase
{
    use PreventSavingStacheItemsToDisk;

    public static function enableProvider()
    {
        return [
            'cp' => [fn () => cp_route('users.two-factor.enable')],
            'frontend' => [fn () => route('statamic.users.two-factor.enable')],
        ];
    }

    public static function confirmProvider()
    {
        return [
            'cp' => [fn () => cp_route('users.two-factor.confirm')],
            'frontend' => [fn () => route('statamic.users.two-factor.confirm')],
        ];
    }

    #[Test]
    #[DataProvider('enableProvider')]
    public function it_enables_two_factor_authentication($url)
    {
        Event::fake();

        $user = $this->user();

        $this->assertNull($user->two_factor_secret);
        $this->assertNull($user->two_factor_recovery_codes);

        $this
            ->actingAs($user)
            ->withActiveElevatedSession()
            ->get($url())
            ->assertOk()
            ->assertJsonStructure(['qr', 'secret_key', 'confirm_url']);

        $this->assertNotNull($user->two_factor_secret);
        $this->assertNotNull($user->two_factor_recovery_codes);

        Event::assertDispatched(TwoFactorAuthenticationEnabled::class, fn ($event) => $event->user->id === $user->id);
    }

    #[Test]
    public function it_cant_enable_two_factor_authentication_without_elevated_session()
    {
        // Elevated sessions are only in the cp.

        Event::fake();

        $user = $this->user();

        $this->assertNull($user->two_factor_secret);
        $this->assertNull($user->two_factor_recovery_codes);

        $this
            ->actingAs($user)
            ->get(cp_route('users.two-factor.enable'))
            ->assertRedirect('/cp/auth/confirm-password');

        $this->assertNull($user->two_factor_secret);
        $this->assertNull($user->two_factor_recovery_codes);

        Event::assertNotDispatched(TwoFactorAuthenticationEnabled::class, fn ($event) => $event->user->id === $user->id);
    }

    #[Test]
    #[DataProvider('enableProvider')]
    public function it_cant_enable_two_factor_authentication_when_it_is_already_enabled($url)
    {
        Event::fake();

        $user = $this->userWithTwoFactorEnabled();

        $this
            ->actingAs($user)
            ->withActiveElevatedSession()
            ->get($url())
            ->assertForbidden();

        Event::assertNotDispatched(TwoFactorAuthenticationEnabled::class, fn ($event) => $event->user->id === $user->id);
    }

    #[Test]
    #[DataProvider('enableProvider')]
    public function it_doesnt_regenerate_secret_when_validation_error_is_present($url)
    {
        Event::fake();

        $user = $this->user();
        $user->set('two_factor_secret', $originalSecret = encrypt(app(TwoFactorAuthenticationProvider::class)->generateSecretKey()));
        $user->set('two_factor_recovery_codes', $originalRecoveryCodes = encrypt(['abc', 'def', 'ghi', 'jkl', 'mno', 'pqr', 'stu', 'vwx']));
        $user->save();

        $this
            ->actingAs($user)
            ->withActiveElevatedSession()
            ->session([
                'errors' => collect(['code' => 'The provided two factor authentication code was invalid.']),
            ])
            ->get($url())
            ->assertOk()
            ->assertJsonStructure(['qr', 'secret_key', 'confirm_url']);

        $this->assertEquals($originalSecret, $user->two_factor_secret);
        $this->assertEquals($originalRecoveryCodes, $user->two_factor_recovery_codes);

        Event::assertNotDispatched(TwoFactorAuthenticationEnabled::class, fn ($event) => $event->user->id === $user->id);
    }

    #[Test]
    #[DataProvider('confirmProvider')]
    public function it_confirms_two_factor_authentication($url)
    {
        $user = $this->user();
        $user->set('two_factor_secret', encrypt(app(TwoFactorAuthenticationProvider::class)->generateSecretKey()));
        $user->set('two_factor_recovery_codes', encrypt(['abc', 'def', 'ghi', 'jkl', 'mno', 'pqr', 'stu', 'vwx']));
        $user->save();

        $this->assertNull($user->two_factor_confirmed_at);

        $this
            ->actingAs($user)
            ->withActiveElevatedSession()
            ->post($url(), [
                'code' => $this->getOneTimeCode($user),
            ])
            ->assertOk();

        $this->assertNotNull($user->two_factor_confirmed_at);
    }

    #[Test]
    #[DataProvider('confirmProvider')]
    public function it_cant_confirm_two_factor_authentication_without_valid_code($url)
    {
        $user = $this->user();
        $user->set('two_factor_secret', encrypt(app(TwoFactorAuthenticationProvider::class)->generateSecretKey()));
        $user->set('two_factor_recovery_codes', encrypt(['abc', 'def', 'ghi', 'jkl', 'mno', 'pqr', 'stu', 'vwx']));
        $user->save();

        $this->assertNull($user->two_factor_confirmed_at);

        $this
            ->actingAs($user)
            ->withActiveElevatedSession()
            ->post($url(), [
                'code' => '123456',
            ])
            ->assertSessionHasErrors('code');

        $this->assertNull($user->two_factor_confirmed_at);
    }

    #[Test]
    public function it_cant_confirm_two_factor_authentication_without_elevated_session()
    {
        // Elevated sessions are only in the cp.

        $user = $this->user();
        $user->set('two_factor_secret', encrypt(app(TwoFactorAuthenticationProvider::class)->generateSecretKey()));
        $user->set('two_factor_recovery_codes', encrypt(['abc', 'def', 'ghi', 'jkl', 'mno', 'pqr', 'stu', 'vwx']));
        $user->save();

        $this->assertNull($user->two_factor_confirmed_at);

        $this
            ->actingAs($user)
            ->post(cp_route('users.two-factor.confirm'), [
                'code' => $this->getOneTimeCode($user),
            ])
            ->assertRedirect('/cp/auth/confirm-password');

        $this->assertNull($user->two_factor_confirmed_at);
    }

    private function user()
    {
        return tap(User::make()->makeSuper()->email('david@hasselhoff.com'))->save();
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

    private function withActiveElevatedSession()
    {
        return $this->session(['statamic_elevated_session' => now()->timestamp]);
    }
}

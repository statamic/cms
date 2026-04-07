<?php

namespace Tests\Auth;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Orchestra\Testbench\Attributes\DefineEnvironment;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Auth\File\Passkey;
use Statamic\Auth\Passwords\PasswordReset;
use Statamic\Auth\TwoFactor\RecoveryCode;
use Statamic\Contracts\Auth\TwoFactor\TwoFactorAuthenticationProvider;
use Statamic\Events\TwoFactorAuthenticationChallenged;
use Statamic\Facades\User;
use Symfony\Component\Uid\Uuid;
use Tests\PreventSavingStacheItemsToDisk;
use Tests\TestCase;
use Webauthn\PublicKeyCredentialSource;
use Webauthn\TrustPath\EmptyTrustPath;

class ResetPasswordTest extends TestCase
{
    use PreventSavingStacheItemsToDisk;

    public static function resetPasswordProvider(): array
    {
        return [
            'cp' => ['cp'],
            'web' => ['web'],
        ];
    }

    private function resetUrl(string $type): string
    {
        return match ($type) {
            'cp' => cp_route('password.reset.action'),
            'web' => route('statamic.password.reset.action'),
        };
    }

    private function twoFactorChallengeUrl(string $type): string
    {
        return match ($type) {
            'cp' => cp_route('two-factor-challenge'),
            'web' => route('statamic.two-factor-challenge'),
        };
    }

    private function broker(string $type)
    {
        $broker = config('statamic.users.passwords.'.PasswordReset::BROKER_RESETS);

        if (is_array($broker)) {
            $broker = $broker[$type];
        }

        return Password::broker($broker);
    }

    #[Test]
    #[DataProvider('resetPasswordProvider')]
    public function it_resets_password_and_logs_in(string $type)
    {
        $user = $this->user();
        $token = $this->broker($type)->createToken($user);

        $this
            ->assertGuest()
            ->post($this->resetUrl($type), [
                'token' => $token,
                'email' => $user->email(),
                'password' => 'newpassword',
                'password_confirmation' => 'newpassword',
            ])
            ->assertRedirect('/');

        $this->assertAuthenticatedAs($user);
        $this->assertTrue(Hash::check('newpassword', $user->fresh()->password()));
    }

    #[Test]
    #[Group('2fa')]
    #[DataProvider('resetPasswordProvider')]
    public function it_redirects_to_two_factor_challenge_when_user_has_two_factor_enabled(string $type)
    {
        Event::fake();

        $user = $this->userWithTwoFactorEnabled();
        $token = $this->broker($type)->createToken($user);

        $this
            ->assertGuest()
            ->post($this->resetUrl($type), [
                'token' => $token,
                'email' => $user->email(),
                'password' => 'newpassword',
                'password_confirmation' => 'newpassword',
            ])
            ->assertRedirect($this->twoFactorChallengeUrl($type))
            ->assertSessionHas('login.id', $user->id())
            ->assertSessionHas('login.remember', false);

        $this->assertGuest();
        $this->assertTrue(Hash::check('newpassword', $user->fresh()->password()));

        Event::assertDispatched(TwoFactorAuthenticationChallenged::class, fn ($event) => $event->user->id === $user->id);
    }

    #[Test]
    #[Group('2fa')]
    #[DefineEnvironment('disableTwoFactor')]
    #[DataProvider('resetPasswordProvider')]
    public function it_skips_two_factor_challenge_when_two_factor_is_disabled(string $type)
    {
        Event::fake();

        $user = $this->userWithTwoFactorEnabled();
        $token = $this->broker($type)->createToken($user);

        $this
            ->assertGuest()
            ->post($this->resetUrl($type), [
                'token' => $token,
                'email' => $user->email(),
                'password' => 'newpassword',
                'password_confirmation' => 'newpassword',
            ])
            ->assertRedirect('/');

        $this->assertAuthenticatedAs($user);
        $this->assertTrue(Hash::check('newpassword', $user->fresh()->password()));

        Event::assertNotDispatched(TwoFactorAuthenticationChallenged::class);
    }

    #[Test]
    #[DefineEnvironment('disallowPasswordLoginWithPasskey')]
    #[DataProvider('resetPasswordProvider')]
    public function it_does_not_log_in_when_user_has_passkeys_and_password_login_is_disallowed(string $type)
    {
        $user = $this->userWithPasskey();
        $token = $this->broker($type)->createToken($user);

        $this
            ->assertGuest()
            ->post($this->resetUrl($type), [
                'token' => $token,
                'email' => $user->email(),
                'password' => 'newpassword',
                'password_confirmation' => 'newpassword',
            ])
            ->assertRedirect($this->loginUrl($type));

        $this->assertGuest();
        $this->assertTrue(Hash::check('newpassword', $user->fresh()->password()));
    }

    #[Test]
    #[DataProvider('resetPasswordProvider')]
    public function it_logs_in_when_user_has_passkeys_and_password_login_is_allowed(string $type)
    {
        $user = $this->userWithPasskey();
        $token = $this->broker($type)->createToken($user);

        $this
            ->assertGuest()
            ->post($this->resetUrl($type), [
                'token' => $token,
                'email' => $user->email(),
                'password' => 'newpassword',
                'password_confirmation' => 'newpassword',
            ])
            ->assertRedirect('/');

        $this->assertAuthenticatedAs($user);
        $this->assertTrue(Hash::check('newpassword', $user->fresh()->password()));
    }

    protected function disableTwoFactor($app)
    {
        $app['config']->set('statamic.users.two_factor_enabled', false);
    }

    protected function disallowPasswordLoginWithPasskey($app)
    {
        $app['config']->set('statamic.webauthn.allow_password_login_with_passkey', false);
    }

    private function loginUrl(string $type): string
    {
        return match ($type) {
            'cp' => cp_route('login'),
            'web' => route('statamic.site'),
        };
    }

    private function user()
    {
        return tap(User::make()->makeSuper()->email('david@hasselhoff.com')->password('secret'))->save();
    }

    private function userWithPasskey()
    {
        $user = $this->user();

        $credential = PublicKeyCredentialSource::create(
            publicKeyCredentialId: 'test-credential-id',
            type: 'public-key',
            transports: ['usb'],
            attestationType: 'none',
            trustPath: new EmptyTrustPath(),
            aaguid: Uuid::fromString('00000000-0000-0000-0000-000000000000'),
            credentialPublicKey: 'test-public-key',
            userHandle: $user->id(),
            counter: 0
        );

        $passkey = (new Passkey)->setUser($user)->setName('Test Key')->setCredential($credential);
        $passkey->save();

        return $user->fresh();
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
}

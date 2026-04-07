<?php

namespace Tests\Auth;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Auth\File\Passkey;
use Statamic\Auth\Passwords\PasswordReset;
use Statamic\Auth\TwoFactor\RecoveryCode;
use Statamic\Contracts\Auth\TwoFactor\TwoFactorAuthenticationProvider;
use Statamic\Facades\User;
use Symfony\Component\Uid\Uuid;
use Tests\PreventSavingStacheItemsToDisk;
use Tests\TestCase;
use Webauthn\PublicKeyCredentialSource;
use Webauthn\TrustPath\EmptyTrustPath;

class ResetPasswordTest extends TestCase
{
    use PreventSavingStacheItemsToDisk;

    public static function resetPasswordProvider()
    {
        return [
            'cp' => ['cp'],
            'web' => ['web'],
        ];
    }

    private function resetUrl($type)
    {
        return match ($type) {
            'cp' => cp_route('password.reset.action'),
            'web' => route('statamic.password.reset.action'),
        };
    }

    private function defaultRedirectUrl($type)
    {
        return match ($type) {
            'cp' => cp_route('login'),
            'web' => route('statamic.site'),
        };
    }

    private function createUser()
    {
        return tap(User::make()->makeSuper()->email('san@holo.com')->password('secret'))->save();
    }

    private function enableTwoFactor($user)
    {
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

    private function addPasskey($user)
    {
        $credential = PublicKeyCredentialSource::create(
            publicKeyCredentialId: 'test-credential-id',
            type: 'public-key',
            transports: ['usb'],
            attestationType: 'none',
            trustPath: new EmptyTrustPath(),
            aaguid: Uuid::fromString('00000000-0000-0000-0000-000000000000'),
            credentialPublicKey: 'test-public-key',
            userHandle: $user->id(),
            counter: 0,
        );

        $passkey = (new Passkey)->setUser($user)->setName('Test Key')->setCredential($credential);
        $passkey->save();

        return $user->fresh();
    }

    private function createToken($user, $type)
    {
        $broker = config('statamic.users.passwords.'.PasswordReset::BROKER_RESETS);

        if (is_array($broker)) {
            $broker = $broker[$type];
        }

        return Password::broker($broker)->createToken($user);
    }

    #[Test]
    #[DataProvider('resetPasswordProvider')]
    public function it_resets_the_password_and_user_is_not_authenticated($type)
    {
        $user = $this->createUser();
        $token = $this->createToken($user, $type);

        $this
            ->assertGuest()
            ->post($this->resetUrl($type), [
                'token' => $token,
                'email' => 'san@holo.com',
                'password' => 'newpassword',
                'password_confirmation' => 'newpassword',
            ])
            ->assertSessionHas('status')
            ->assertRedirect($this->defaultRedirectUrl($type));

        $this->assertGuest();
        $this->assertTrue(Hash::check('newpassword', $user->fresh()->password()));
    }

    #[Test]
    #[DataProvider('resetPasswordProvider')]
    public function it_resets_password_for_two_factor_user_and_user_is_not_authenticated($type)
    {
        $user = $this->enableTwoFactor($this->createUser());
        $token = $this->createToken($user, $type);

        $this
            ->assertGuest()
            ->post($this->resetUrl($type), [
                'token' => $token,
                'email' => 'san@holo.com',
                'password' => 'newpassword',
                'password_confirmation' => 'newpassword',
            ])
            ->assertSessionHas('status')
            ->assertRedirect($this->defaultRedirectUrl($type));

        $this->assertGuest();
        $this->assertTrue(Hash::check('newpassword', $user->fresh()->password()));
    }

    #[Test]
    #[DataProvider('resetPasswordProvider')]
    public function it_resets_password_for_passkey_user_and_user_is_not_authenticated($type)
    {
        config(['statamic.webauthn.allow_password_login_with_passkey' => false]);

        $user = $this->addPasskey($this->createUser());
        $token = $this->createToken($user, $type);

        $this
            ->assertGuest()
            ->post($this->resetUrl($type), [
                'token' => $token,
                'email' => 'san@holo.com',
                'password' => 'newpassword',
                'password_confirmation' => 'newpassword',
            ])
            ->assertSessionHas('status')
            ->assertRedirect($this->defaultRedirectUrl($type));

        $this->assertGuest();
        $this->assertTrue(Hash::check('newpassword', $user->fresh()->password()));
    }
}

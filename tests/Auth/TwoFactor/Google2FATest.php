<?php

namespace Tests\Auth\TwoFactor;

use Illuminate\Support\Collection;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Auth\TwoFactor\Google2FA;
use Statamic\Auth\TwoFactor\RecoveryCode;
use Statamic\Exceptions\TwoFactorNotSetupException;
use Statamic\Facades\User;
use Tests\PreventSavingStacheItemsToDisk;
use Tests\TestCase;

class Google2FATest extends TestCase
{
    use PreventSavingStacheItemsToDisk;

    private $provider;

    protected function setUp(): void
    {
        parent::setUp();

        $this->provider = app(Google2FA::class);
    }

    #[Test]
    public function it_can_generate_a_secret_key()
    {
        // Generate and test the key.
        $key1 = $this->provider->generateSecretKey();

        $this->assertIsString($key1);

        // Expect the second key to be different.
        $key2 = $this->provider->generateSecretKey();

        $this->assertIsString($key2);
        $this->assertNotEquals($key1, $key2);
    }

    #[Test]
    public function it_cannot_return_a_otpauth_url_when_user_is_not_yet_setup()
    {
        $this->actingAs($this->user());

        $this->expectException(TwoFactorNotSetupException::class);

        $this->provider->getQrCode();
    }

    #[Test]
    public function it_can_return_a_otpauth_url_when_user_is_setup()
    {
        $this->actingAs($user = $this->userWithTwoFactorEnabled());

        $url = $this->provider->getQrCode();

        $this->assertStringContainsString('otpauth://totp/Laravel:'.$user->email, $url);
        $this->assertStringContainsString('secret='.decrypt($user->two_factor_secret), $url);
    }

    #[Test]
    public function it_can_return_the_svg_markup_for_the_qr_code()
    {
        $this->actingAs($this->userWithTwoFactorEnabled());

        $url = $this->provider->getQrCodeSvg();

        $this->assertStringStartsWith('<svg', $url);
    }

    #[Test]
    public function it_can_verify_a_one_time_code()
    {
        $this->actingAs($this->userWithTwoFactorEnabled());

        $code = '111111';
        while ($code === '111111') {
            // Create a code that is NOT 111111.
            $code = $this->getOneTimeCode();
        }

        // Should verify correctly.
        $this->assertNotEquals('111111', $code);
        $this->assertTrue($this->provider->verify($this->provider->getSecretKey(), $code));

        // Try with '111111', and should fail.
        $code = '111111';

        $this->assertEquals('111111', $code);
        $this->assertFalse($this->provider->verify($this->provider->getSecretKey(), $code));
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

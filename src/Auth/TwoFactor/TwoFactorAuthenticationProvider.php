<?php

namespace Statamic\Auth\TwoFactor;

use Illuminate\Cache\Repository;
use PragmaRX\Google2FA\Google2FA;
use Statamic\Contracts\Auth\TwoFactor\TwoFactorAuthenticationProvider as Contract;

class TwoFactorAuthenticationProvider implements Contract
{
    public function __construct(private Google2FA $engine, private ?Repository $cache = null)
    {
    }

    /**
     * Generate a new secret key.
     */
    public function generateSecretKey(int $secretLength = 16): string
    {
        return $this->engine->generateSecretKey($secretLength);
    }

    /**
     * Get the two factor authentication QR code URL.
     */
    public function qrCodeUrl(
        string $name,
        string $email,
        #[\SensitiveParameter]
        string $secret
    ): string {
        return $this->engine->getQRCodeUrl($name, $email, $secret);
    }

    /**
     * Verify the given code.
     */
    public function verify(
        #[\SensitiveParameter]
        string $secret,
        #[\SensitiveParameter]
        string $code
    ): bool {
        $timestamp = $this->engine->verifyKeyNewer(
            $secret, $code, optional($this->cache)->get($key = 'statamic.2fa_codes.'.md5($code))
        );

        if ($timestamp !== false) {
            if ($timestamp === true) {
                $timestamp = $this->engine->getTimestamp();
            }

            optional($this->cache)->put($key, $timestamp, ($this->engine->getWindow() ?: 1) * 60);

            return true;
        }

        return false;
    }
}

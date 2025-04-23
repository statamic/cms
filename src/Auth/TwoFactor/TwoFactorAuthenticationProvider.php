<?php

namespace Statamic\Auth\TwoFactor;

use Illuminate\Cache\Repository;
use PragmaRX\Google2FA\Google2FA;

class TwoFactorAuthenticationProvider
{
    /**
     * The underlying library providing two factor authentication helper services.
     *
     * @var Google2FA
     */
    protected $engine;

    /**
     * The cache repository implementation.
     *
     * @var \Illuminate\Contracts\Cache\Repository|null
     */
    protected $cache;

    public function __construct(Google2FA $engine, ?Repository $cache = null)
    {
        $this->engine = $engine;
        $this->cache = $cache;
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
    public function qrCodeUrl(string $companyName, string $companyEmail, string $secret): string
    {
        return $this->engine->getQRCodeUrl($companyName, $companyEmail, $secret);
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

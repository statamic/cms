<?php

namespace Statamic\Contracts\Auth\TwoFactor;

interface TwoFactorAuthenticationProvider
{
    public function generateSecretKey(int $secretLength = 16): string;

    public function qrCodeUrl(
        string $name,
        string $email,
        #[\SensitiveParameter] string $secret
    ): string;

    public function verify(
        #[\SensitiveParameter] string $secret,
        #[\SensitiveParameter] string $code
    ): bool;
}

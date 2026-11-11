<?php

namespace Statamic\Forms\Captcha;

use Illuminate\Support\Facades\Http;
use Statamic\Contracts\Forms\Captcha;
use Throwable;

/**
 * A captcha checked by posting its token to the provider's "siteverify"
 * endpoint, which is how Turnstile, hCaptcha and reCAPTCHA all work.
 */
abstract class SiteverifyCaptcha implements Captcha
{
    /**
     * Providers cap their tokens well below this, so anything longer isn't
     * worth sending.
     */
    const MAX_TOKEN_LENGTH = 8192;

    public function __construct(protected array $config = [])
    {
        //
    }

    abstract protected function endpoint(): string;

    public function verify(?string $token, ?string $ip = null): bool
    {
        if ($token === null || $token === '' || strlen($token) > static::MAX_TOKEN_LENGTH) {
            return false;
        }

        try {
            $response = Http::asForm()
                ->timeout($this->timeout())
                ->connectTimeout($this->timeout())
                ->post($this->endpoint(), $this->payload($token, $ip));
        } catch (Throwable $e) {
            return false;
        }

        if (! $response->successful() || ! is_array($result = $response->json())) {
            return false;
        }

        return $this->passed($result);
    }

    protected function payload(string $token, ?string $ip): array
    {
        return array_filter([
            'secret' => $this->secretKey(),
            'response' => $token,
            'remoteip' => $ip,
        ], fn ($value) => $value !== null);
    }

    protected function passed(array $result): bool
    {
        return ($result['success'] ?? false) === true;
    }

    protected function siteKey(): string
    {
        return (string) ($this->config['site_key'] ?? '');
    }

    protected function secretKey(): string
    {
        return (string) ($this->config['secret_key'] ?? '');
    }

    protected function timeout(): int|float
    {
        $timeout = $this->config['timeout'] ?? 5;

        return is_numeric($timeout) && $timeout > 0 ? $timeout + 0 : 5;
    }

    protected function attr(string $value): string
    {
        return htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }

    protected function js(string $value): string
    {
        return json_encode($value, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT);
    }
}

<?php

namespace Statamic\Licensing;

use Illuminate\Support\Facades\File;

class SiteKey
{
    public const PREFIX = 'site_';

    public const ENTROPY_LENGTH = 26;

    public function generate(): string
    {
        $alphabet = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
        $entropy = '';

        for ($i = 0; $i < self::ENTROPY_LENGTH; $i++) {
            $entropy .= $alphabet[random_int(0, strlen($alphabet) - 1)];
        }

        return self::PREFIX.$entropy;
    }

    public function isValid(?string $key): bool
    {
        return (bool) preg_match('/^'.preg_quote(self::PREFIX, '/').'[a-zA-Z0-9]{'.self::ENTROPY_LENGTH.'}$/', (string) $key);
    }

    public function runningInCi(): bool
    {
        if (app()->runningUnitTests()) {
            return filter_var($_SERVER['STATAMIC_TEST_CI'] ?? false, FILTER_VALIDATE_BOOLEAN);
        }

        return filter_var($_SERVER['CI'] ?? $_ENV['CI'] ?? getenv('CI'), FILTER_VALIDATE_BOOLEAN);
    }

    /**
     * Make sure this install has a site key, minting one if needed.
     *
     * Nothing is minted in CI, or when the install already uses a legacy
     * STATAMIC_LICENSE_KEY (those sites opt in to site keys from statamic.com).
     */
    public function ensure(?string $envPath = null, ?string $examplePath = null): ?string
    {
        $envPath ??= base_path('.env');
        $examplePath ??= base_path('.env.example');

        if ($this->runningInCi() || $this->hasLegacyLicenseKey($envPath)) {
            return $this->populatedValue($envPath)
                ?? $this->populatedValue($examplePath);
        }

        $envPath ??= base_path('.env');
        $examplePath ??= base_path('.env.example');

        $existing = $this->populatedValue($envPath) ?? $this->populatedValue($examplePath);

        if ($existing) {
            $this->fillIfBlank($envPath, $existing);
            $this->fillIfBlank($examplePath, $existing);

            return $existing;
        }

        return $this->mint($envPath, $examplePath);
    }

    public function hasLegacyLicenseKey(?string $envPath = null): bool
    {
        $envPath ??= base_path('.env');

        return (bool) $this->populatedValue($envPath, 'STATAMIC_LICENSE_KEY');
    }

    /**
     * Mint a site key for this install.
     *
     * Only reads STATAMIC_SITE_KEY from .env — never .env.example. Cloned repos
     * pick up a shared key through ensure(), but an explicit mint always creates
     * a fresh identity so "Generate site key" does not resurrect a claimed example key.
     */
    public function mint(?string $envPath = null, ?string $examplePath = null): string
    {
        $envPath ??= base_path('.env');
        $examplePath ??= base_path('.env.example');

        if ($key = $this->populatedValue($envPath)) {
            $this->fillIfBlank($examplePath, $key);

            return $key;
        }

        return $this->write($this->generate(), $envPath, $examplePath);
    }

    public function write(string $key, ?string $envPath = null, ?string $examplePath = null): string
    {
        $envPath ??= base_path('.env');
        $examplePath ??= base_path('.env.example');

        $this->writeKey($envPath, $key);
        $this->writeKey($examplePath, $key);

        return $key;
    }

    private function populatedValue(string $path, string $variable = 'STATAMIC_SITE_KEY'): ?string
    {
        if (! File::exists($path)) {
            return null;
        }

        if (! preg_match('/^'.preg_quote($variable, '/').'=(.+)$/m', File::get($path), $matches)) {
            return null;
        }

        $value = trim($matches[1], " \t\"'");

        return $value !== '' ? $value : null;
    }

    private function fillIfBlank(string $path, string $key): void
    {
        if ($this->populatedValue($path)) {
            return;
        }

        $this->writeKey($path, $key);
    }

    private function writeKey(string $path, string $key): void
    {
        $line = 'STATAMIC_SITE_KEY='.$key;

        if (! File::exists($path)) {
            File::put($path, $line."\n");

            return;
        }

        $contents = File::get($path);

        if (preg_match('/^#?\s*STATAMIC_SITE_KEY=.*$/m', $contents)) {
            File::put($path, preg_replace('/^#?\s*STATAMIC_SITE_KEY=.*$/m', $line, $contents, 1));

            return;
        }

        File::put($path, rtrim($contents)."\n".$line."\n");
    }
}

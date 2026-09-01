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

    public function ensure(?string $envPath = null, ?string $examplePath = null): ?string
    {
        $envPath ??= base_path('.env');
        $examplePath ??= base_path('.env.example');

        $existing = $this->populatedValue($envPath)
            ?? $this->populatedValue($examplePath);

        if ($this->runningInCi()) {
            return $existing;
        }

        $key = $existing ?? $this->generate();

        $this->fillIfBlank($envPath, $key);
        $this->fillIfBlank($examplePath, $key);

        return $key;
    }

    public function write(string $key, ?string $envPath = null, ?string $examplePath = null): string
    {
        $envPath ??= base_path('.env');
        $examplePath ??= base_path('.env.example');

        $this->writeKey($envPath, $key);
        $this->writeKey($examplePath, $key);

        return $key;
    }

    private function populatedValue(string $path): ?string
    {
        if (! File::exists($path)) {
            return null;
        }

        if (! preg_match('/^STATAMIC_SITE_KEY=(.+)$/m', File::get($path), $matches)) {
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

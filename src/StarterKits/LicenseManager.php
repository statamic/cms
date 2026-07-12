<?php

namespace Statamic\StarterKits;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Statamic\Console\NullConsole;

use function Laravel\Prompts\text;

final class LicenseManager
{
    const OUTPOST_ENDPOINT = 'https://outpost.statamic.com/v3/starter-kits/';

    private $package;
    private $licenseKey;
    private $console;
    private $isInteractive;
    private $details;
    private $valid = false;

    /**
     * Instantiate starter kit license manager.
     */
    public function __construct(string $package, ?string $licenseKey = null, ?Command $console = null, bool $isInteractive = false)
    {
        $this->package = $package;
        $this->licenseKey = $licenseKey ?? config('statamic.system.license_key');
        $this->console = $console ?? new NullConsole;
        $this->isInteractive = $isInteractive;
    }

    /**
     * Instantiate starter kit license manager.
     */
    public static function validate(string $package, ?string $licenceKey = null, ?Command $console = null, bool $isInteractive = false): self
    {
        return (new self($package, $licenceKey, $console, $isInteractive))->performValidation();
    }

    /**
     * Check if user is able to install starter kit, whether free or paid.
     */
    public function isValid(): bool
    {
        return $this->valid;
    }

    /**
     * Expire license key and increment install count.
     */
    public function completeInstall(): void
    {
        Http::post(self::OUTPOST_ENDPOINT.'installed', [
            'license' => $this->licenseKey,
            'configured_site_license' => config('statamic.system.license_key'),
            'package' => $this->package,
        ]);
    }

    /**
     * Perform validation.
     */
    private function performValidation(): self
    {
        if (! $this->outpostGetStarterKitDetails()) {
            return $this->error('Cannot connect to [statamic.com] to validate license!');
        }

        if ($this->isFreeStarterKit()) {
            return $this->clearLicenseKey()->setValid();
        }

        $this->info('Validating starter kit license...');

        $sellerSlug = $this->details['seller']['slug'];
        $kitSlug = $this->details['slug'];
        $marketplaceUrl = "https://statamic.com/starter-kits/{$sellerSlug}/{$kitSlug}";

        if (! $this->licenseKey) {
            if (! $this->isInteractive) {
                return $this
                    ->error("License required for [{$this->package}]!")
                    ->comment('This is a paid starter kit. If you haven\'t already, you may purchase a license at:')
                    ->comment($marketplaceUrl);
            }

            $this
                ->comment('This is a paid starter kit. If you haven\'t already, you may purchase a license at:')
                ->comment($marketplaceUrl);

            $this->licenseKey = text('Please enter your license key', required: true);
        }

        if ($this->outpostValidatesLicense()) {
            return $this->info('Starter kit license valid!')->setValid();
        }

        return $this
            ->error("Invalid license for [{$this->package}]!")
            ->comment('If you haven\'t already, you may purchase a license at:')
            ->comment($marketplaceUrl);
    }

    /**
     * Get starter kit details from outpost.
     */
    private function outpostGetStarterKitDetails(): self
    {
        $response = Http::get(self::OUTPOST_ENDPOINT.$this->package);

        if ($response->status() !== 200) {
            return false;
        }

        $this->details = $response['data'];

        return $this;
    }

    /**
     * Check if starter kit is a free starter kit.
     */
    private function isFreeStarterKit(): bool
    {
        if ($this->details === false) {
            return true;
        }

        return ! $this->details['price'];
    }

    /**
     * Check if outpost validates kit license.
     */
    private function outpostValidatesLicense(): bool
    {
        if (! $this->licenseKey) {
            return false;
        }

        $response = Http::post(self::OUTPOST_ENDPOINT.'validate', [
            'license' => $this->licenseKey,
            'configured_site_license' => config('statamic.system.license_key'),
            'package' => $this->package,
        ]);

        if ($response->status() !== 200) {
            return false;
        }

        return $response['data']['valid'] === true;
    }

    /**
     * Clear license key.
     */
    private function clearLicenseKey(): self
    {
        $this->licenseKey = null;

        return $this;
    }

    /**
     * Set validated status to true.
     */
    private function setValid(): self
    {
        $this->valid = true;

        return $this;
    }

    /**
     * Output info message.
     */
    private function info(string $message): self
    {
        $this->console->info($message);

        return $this;
    }

    /**
     * Output error message.
     */
    private function error(string $message): self
    {
        $this->console->error($message);

        return $this;
    }

    /**
     * Output comment line.
     */
    private function comment(string $message): self
    {
        $this->console->comment($message);

        return $this;
    }
}

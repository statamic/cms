<?php

namespace Statamic\StarterKits;

use Illuminate\Support\Facades\Http;
use Statamic\Console\NullConsole;

final class LicenseManager
{
    const OUTPOST_ENDPOINT = 'https://outpost.statamic.com/v3/starter-kits/';

    private $package;
    private $licenseKey;
    private $console;
    private $details;
    private $valid = false;

    /**
     * Instantiate starter kit license manager.
     *
     * @param  string  $package
     * @param  string|null  $licenseKey
     * @param  mixed  $console
     */
    public function __construct(string $package, $licenseKey = null, $console = null)
    {
        $this->package = $package;
        $this->licenseKey = $licenseKey ?? config('statamic.system.license_key');
        $this->console = $console ?? new NullConsole;
    }

    /**
     * Instantiate starter kit license manager.
     *
     * @param  string  $package
     * @param  string|null  $licenceKey
     * @param  mixed  $console
     * @return static
     */
    public static function validate(string $package, $licenceKey = null, $console = null)
    {
        return (new static($package, $licenceKey, $console))->performValidation();
    }

    /**
     * Check if user is able to install starter kit, whether free or paid.
     *
     * @return bool
     */
    public function isValid()
    {
        return $this->valid;
    }

    /**
     * Expire license key and increment install count.
     */
    public function completeInstall()
    {
        Http::post(self::OUTPOST_ENDPOINT.'installed', [
            'license' => $this->licenseKey,
            'configured_site_license' => config('statamic.system.license_key'),
            'package' => $this->package,
        ]);
    }

    /**
     * Perform validation.
     *
     * @return $this
     */
    private function performValidation()
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
            return $this
                ->error("License required for [{$this->package}]!")
                ->comment('This is a paid starter kit. If you haven\'t already, you may purchase a license at:')
                ->comment($marketplaceUrl);
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
     *
     * @return $this
     */
    private function outpostGetStarterKitDetails()
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
     *
     * @return bool
     */
    private function isFreeStarterKit()
    {
        if ($this->details === false) {
            return true;
        }

        return ! $this->details['price'];
    }

    /**
     * Check if outpost validates kit license.
     *
     * @return bool
     */
    private function outpostValidatesLicense()
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
     *
     * @return $this
     */
    private function clearLicenseKey()
    {
        $this->licenseKey = null;

        return $this;
    }

    /**
     * Set validated status to true.
     *
     * @return $this
     */
    private function setValid()
    {
        $this->valid = true;

        return $this;
    }

    /**
     * Output info message.
     *
     * @param  string  $message
     * @return $this
     */
    private function info(string $message)
    {
        $this->console->info($message);

        return $this;
    }

    /**
     * Output error message.
     *
     * @param  string  $message
     * @return $this
     */
    private function error(string $message)
    {
        $this->console->error($message);

        return $this;
    }

    /**
     * Output comment line.
     *
     * @param  string  $message
     * @return $this
     */
    private function comment(string $message)
    {
        $this->console->comment($message);

        return $this;
    }
}

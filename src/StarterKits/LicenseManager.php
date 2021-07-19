<?php

namespace Statamic\StarterKits;

use Illuminate\Support\Facades\Http;
use Statamic\Console\NullConsole;

final class LicenseManager
{
    const OUTPOST_ENDPOINT = 'https://outpost.statamic.com/v3/starter-kits/';

    private $package;
    private $kitLicenseKey;
    private $siteLicenseKey;
    private $console;
    private $details;
    private $valid = false;

    /**
     * Instantiate starter kit license manager.
     *
     * @param string $package
     * @param string|null $kitLicenseKey
     * @param mixed $console
     */
    public function __construct(string $package, $kitLicenseKey, $console = null)
    {
        $this->package = $package;
        $this->kitLicenseKey = $kitLicenseKey;
        $this->siteLicenseKey = config('statamic.system.license_key');
        $this->console = $console ?? new NullConsole;
    }

    /**
     * Instantiate starter kit license manager.
     *
     * @param string $package
     * @param string|null $kitLicenseKey
     * @param mixed $console
     * @return static
     */
    public static function validate(string $package, $kitLicenceKey, $console = null)
    {
        return (new static($package, $kitLicenceKey, $console))->performValidation();
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
     * Check if user has valid starter kit license key, for the purpose of expiring after a successful install.
     *
     * @return bool
     */
    public function hasValidKitLicenseKey()
    {
        return $this->valid && $this->kitLicenseKey;
    }

    /**
     * Expire license key.
     */
    public function expireLicense()
    {
        if (! $this->hasValidKitLicenseKey()) {
            return;
        }

        Http::post(self::OUTPOST_ENDPOINT.'expire', [
            'package' => $this->package,
            'kit_license' => $this->kitLicenseKey,
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
            return $this->clearKitLicenseKey()->setValid(false);
        }

        $this->info('Validating starter kit license...');

        if (! $this->kitLicenseKey && ! $this->siteLicenseKey) {
            return $this
                ->error('Cannot find site license!')
                ->comment('This is a paid starter kit. If you haven\'t already, you may purchase a license at:')
                ->comment("https://statamic.com/starter-kits/{$this->package}");
        }

        if ($this->outpostValidatesKitLicense() || $this->outpostValidatesSiteLicense()) {
            return $this->setValid();
        }

        return $this
            ->error("Invalid license for [{$this->package}]!")
            ->comment('If you haven\'t already, you may purchase a license at:')
            ->comment("https://statamic.com/starter-kits/{$this->package}");
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
    private function outpostValidatesSiteLicense()
    {
        if (! $this->siteLicenseKey) {
            return false;
        }

        $response = Http::post(self::OUTPOST_ENDPOINT.'validate/site', [
            'package' => $this->package,
            'site_license' => $this->siteLicenseKey,
        ]);

        if ($response->status() !== 200) {
            return false;
        }

        if (! $this->kitLicenseKey && $response['data']['kit_license']) {
            $this->kitLicenseKey = $response['data']['kit_license'];
        }

        return $response['data']['valid'] === true;
    }

    /**
     * Check if outpost validates kit license.
     *
     * @return bool
     */
    private function outpostValidatesKitLicense()
    {
        if (! $this->kitLicenseKey) {
            return false;
        }

        $response = Http::post(self::OUTPOST_ENDPOINT.'validate/kit', [
            'package' => $this->package,
            'kit_license' => $this->kitLicenseKey,
        ]);

        if ($response->status() !== 200) {
            return false;
        }

        return $response['data']['valid'] === true;
    }

    /**
     * Clear kit license key.
     *
     * @return $this
     */
    private function clearKitLicenseKey()
    {
        $this->kitLicenseKey = null;

        return $this;
    }

    /**
     * Set validated status to true.
     *
     * @return $this
     */
    private function setValid($outputMessage = true)
    {
        $this->valid = true;

        if ($outputMessage) {
            $this->info('Starter kit license valid!');
        }

        return $this;
    }

    /**
     * Output info message.
     *
     * @param string $message
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
     * @param string $message
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
     * @param string $message
     * @return $this
     */
    private function comment(string $message)
    {
        $this->console->comment($message);

        return $this;
    }
}

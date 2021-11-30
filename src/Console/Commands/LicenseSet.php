<?php

namespace Statamic\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Console\ConfirmableTrait;
use Statamic\Console\EnhancesCommands;
use Statamic\Console\RunsInPlease;

class LicenseSet extends Command
{
    use RunsInPlease, EnhancesCommands, ConfirmableTrait;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'statamic:license:set
        { key : Specify the Statamic license key to set in your .env }
        { --force : Force the operation to run when in production }';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Set Statamic license key in .env';

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle()
    {
        $key = $this->argument('key');

        if (! $this->setKeyInEnvironmentFile($key)) {
            return;
        }

        $this->laravel['config']['statamic.system.license_key'] = $key;

        $this->checkInfo('Statamic license key set successfully.');
    }

    /**
     * Set the application key in the environment file.
     *
     * @param  string  $key
     * @return bool
     */
    protected function setKeyInEnvironmentFile($key)
    {
        if (! $this->confirmToProceed()) {
            return false;
        }

        if ($this->licenseKeyExists()) {
            $this->replaceKeyInEnv($key);
        } else {
            $this->appendKeyToEnv($key);
        }

        return true;
    }

    /**
     * Whether the license key already exists in the .env file.
     *
     * @return bool
     */
    protected function licenseKeyExists()
    {
        return preg_match('/^STATAMIC_LICENSE_KEY=/m', $this->envContents());
    }

    /**
     * Replace key in .env file.
     *
     * @param  string  $key
     * @return void
     */
    protected function replaceKeyInEnv($key)
    {
        file_put_contents($this->envPath(), preg_replace(
            $this->keyReplacementPattern(),
            'STATAMIC_LICENSE_KEY='.$key,
            $this->envContents()
        ));
    }

    /**
     * Append key to end of .env file.
     *
     * @param  string  $key
     * @return void
     */
    protected function appendKeyToEnv($key)
    {
        file_put_contents($this->envPath(), $this->envContents()."\nSTATAMIC_LICENSE_KEY={$key}");
    }

    /**
     * Get app .env path.
     *
     * @return string
     */
    protected function envPath()
    {
        return $this->laravel->environmentFilePath();
    }

    /**
     * Get app .env contents.
     *
     * @return string
     */
    protected function envContents()
    {
        return file_get_contents($this->envPath());
    }

    /**
     * Regex pattern that will match env STATAMIC_LICENSE_KEY.
     *
     * @return string
     */
    protected function keyReplacementPattern()
    {
        $escaped = preg_quote('='.$this->laravel['config']['statamic.system.license_key'], '/');

        return "/^STATAMIC_LICENSE_KEY{$escaped}/m";
    }
}

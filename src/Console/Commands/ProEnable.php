<?php

namespace Statamic\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Console\ConfirmableTrait;
use Statamic\Console\EnhancesCommands;
use Statamic\Console\RunsInPlease;
use Statamic\Support\Str;

class ProEnable extends Command
{
    use ConfirmableTrait, EnhancesCommands, RunsInPlease;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'statamic:pro:enable
        { --force : Force the operation to run when in production }
        { --update-config : Also update editions config to reference .env var }';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Enable Statamic Pro in .env';

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle()
    {
        if (! $this->setProInEnvironmentFile()) {
            return;
        }

        $this->checkInfo('Statamic Pro successfully enabled in .env file!');
        $this->promptToSetLicenseKey();

        if ($this->option('update-config') && $this->updateConfig()) {
            $this->checkInfo('Statamic editions config successfully updated to reference .env var!');
        }

        if ($this->option('update-config') && ! $this->isConfigReferencingEnv()) {
            $this->crossLine('Could not reliably update editions config to reference .env var!');
            $this->comment(PHP_EOL.'For this setting to take effect, please modify your [config/statamic/editions.php] as follows:');
            $this->line("'pro' => env('STATAMIC_PRO_ENABLED', false)");
        } elseif (! $this->isConfigReferencingEnv()) {
            $this->crossLine('Statamic editions config not currently referencing .env var!');
            $this->comment('Please re-run this command with the `--update-config` option.');
        } else {
            config()->set('statamic.editions.pro', true);
        }
    }

    /**
     * Set to pro in the environment file.
     *
     * @return bool
     */
    protected function setProInEnvironmentFile()
    {
        if (! $this->confirmToProceed()) {
            return false;
        }

        if ($this->proEnvVarExists()) {
            $this->ensureProInEnv();
        } else {
            $this->appendProToEnv();
        }

        return true;
    }

    /**
     * Check whether the pro env var already exists.
     *
     * @return bool
     */
    protected function proEnvVarExists()
    {
        return preg_match('/^#?\s*STATAMIC_PRO_ENABLED=/m', $this->envContents());
    }

    /**
     * Ensure pro in .env file.
     *
     * @return void
     */
    protected function ensureProInEnv()
    {
        file_put_contents($this->envPath(), preg_replace(
            '/^#?\s*STATAMIC_PRO_ENABLED=.*$/m',
            'STATAMIC_PRO_ENABLED=true',
            $this->envContents()
        ));
    }

    /**
     * Append pro to end of .env file.
     *
     * @return void
     */
    protected function appendProToEnv()
    {
        file_put_contents($this->envPath(), $this->envContents()."\nSTATAMIC_PRO_ENABLED=true");
    }

    /**
     * Prompt to set the license key in the environment file.
     *
     * @return void
     */
    protected function promptToSetLicenseKey()
    {
        if (! $this->input->isInteractive()) {
            return;
        }

        if ($this->licenseKeyAlreadySet()) {
            return;
        }

        $licenseKey = trim((string) $this->ask('If you have a Statamic license key, paste it now (leave blank to add later)'));

        if ($licenseKey === '') {
            $this->comment('Add `STATAMIC_LICENSE_KEY=...` to your `.env` before or when your site goes live.');

            return;
        }

        if ($this->licenseKeyEnvVarExists()) {
            $this->replaceLicenseKeyInEnv($licenseKey);
        } else {
            $this->appendLicenseKeyToEnv($licenseKey);
        }

        $this->checkInfo('Statamic license key saved in .env file.');
    }

    /**
     * Check whether a non-empty license key already exists.
     *
     * @return bool
     */
    protected function licenseKeyAlreadySet()
    {
        $licenseKey = $this->licenseKeyFromEnv();

        return $licenseKey !== null && $licenseKey !== '';
    }

    /**
     * Check whether the license key env var exists.
     *
     * @return bool
     */
    protected function licenseKeyEnvVarExists()
    {
        return preg_match('/^#?\s*STATAMIC_LICENSE_KEY=/m', $this->envContents());
    }

    /**
     * Replace key in .env file.
     *
     * @param  string  $licenseKey
     * @return void
     */
    protected function replaceLicenseKeyInEnv($licenseKey)
    {
        file_put_contents($this->envPath(), preg_replace_callback(
            '/^#?\s*STATAMIC_LICENSE_KEY=.*$/m',
            fn () => 'STATAMIC_LICENSE_KEY='.$licenseKey,
            $this->envContents()
        ));
    }

    /**
     * Append key to end of .env file.
     *
     * @param  string  $licenseKey
     * @return void
     */
    protected function appendLicenseKeyToEnv($licenseKey)
    {
        file_put_contents($this->envPath(), $this->envContents()."\nSTATAMIC_LICENSE_KEY={$licenseKey}");
    }

    /**
     * Get the license key from .env.
     *
     * @return string|null
     */
    protected function licenseKeyFromEnv()
    {
        preg_match('/^STATAMIC_LICENSE_KEY=(.*)$/m', $this->envContents(), $matches);

        return isset($matches[1]) ? trim($matches[1]) : null;
    }

    /**
     * Get app .env path.
     *
     * @return string
     */
    protected function envPath()
    {
        return app()->environmentFilePath();
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
     * Update editions config to reference the .env var.
     *
     * @return bool
     */
    protected function updateConfig()
    {
        if (! file_exists($configPath = config_path('statamic/editions.php'))) {
            return false;
        }

        if ($this->isConfigReferencingEnv()) {
            return false;
        }

        $contents = file_get_contents($configPath);

        if (Str::contains($contents, "'pro' => false,")) {
            $contents = str_replace("'pro' => false,", "'pro' => env('STATAMIC_PRO_ENABLED', false),", $contents);
        } elseif (Str::contains($contents, "'pro' => true,")) {
            $contents = str_replace("'pro' => true,", "'pro' => env('STATAMIC_PRO_ENABLED', false),", $contents);
        } else {
            return false;
        }

        file_put_contents($configPath, $contents);

        return true;
    }

    /**
     * Check whether the editions config is referencing the .env var.
     *
     * @return bool
     */
    protected function isConfigReferencingEnv()
    {
        if (! file_exists($configPath = config_path('statamic/editions.php'))) {
            return true;
        }

        return (bool) preg_match('/[\'"]pro[\'"]\s*=>\s*env\([\'"]STATAMIC_PRO_ENABLED[\'"]/m', file_get_contents($configPath));
    }
}

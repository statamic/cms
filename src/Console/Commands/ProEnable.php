<?php

namespace Statamic\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Console\ConfirmableTrait;
use Statamic\Console\EnhancesCommands;
use Statamic\Console\RunsInPlease;

class ProEnable extends Command
{
    use ConfirmableTrait, EnhancesCommands, RunsInPlease;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'statamic:pro:enable
        { --force : Force the operation to run when in production }';

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

        $this->laravel['config']['statamic.editions.pro'] = true;

        $this->checkInfo('Statamic Pro successfully enabled in .env file!');

        if ($this->configNotReferencingEnv()) {
            $this->crossLine('Statamic editions config not currently referencing .env file.');
            $this->comment(PHP_EOL.'For this setting to take effect, please modify your [config/statamic/editions.php] as follows:');
            $this->line("'pro' => env('STATAMIC_PRO_ENABLED', false)");
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
        return preg_match('/^STATAMIC_PRO_ENABLED=/m', $this->envContents());
    }

    /**
     * Ensure pro in .env file.
     *
     * @return void
     */
    protected function ensureProInEnv()
    {
        file_put_contents($this->envPath(), preg_replace(
            '/^STATAMIC_PRO_ENABLED=.*$/m',
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
     * Check whether the editions config is referencing the .env var.
     *
     * @return bool
     */
    protected function configNotReferencingEnv()
    {
        if (! file_exists($configPath = config_path('statamic/editions.php'))) {
            return false;
        }

        return ! preg_match('/[\'"]pro[\'"]\s*=>\s*env\([\'"]STATAMIC_PRO_ENABLED[\'"]/m', file_get_contents($configPath));
    }
}

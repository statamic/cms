<?php

namespace Statamic\Console\Commands;

use Illuminate\Console\Command;
use Statamic\Console\RunsInPlease;
use Statamic\Console\ValidatesInput;
use Statamic\Rules\ComposerPackage;
use Statamic\StarterKits\Exceptions\StarterKitException;
use Statamic\StarterKits\Installer as StarterKitInstaller;
use Statamic\StarterKits\LicenseManager as StarterKitLicenseManager;

class StarterKitInstall extends Command
{
    use RunsInPlease, ValidatesInput;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'statamic:starter-kit:install
        { package? : Specify the starter kit package to install }
        { --license= : Provide explicit starter kit license key }
        { --local : Install from local repo configured in composer config.json }
        { --with-config : Copy starter-kit.yaml config for local development }
        { --without-dependencies : Install without dependencies }
        { --force : Force install and allow dependency errors }
        { --clear-site : Clear site before installing }';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Install starter kit';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        if (version_compare(app()->version(), '7', '<')) {
            return $this->error('Laravel 7+ is required to install starter kits!');
        }

        if ($this->validationFails($package = $this->getPackage(), new ComposerPackage)) {
            return;
        }

        $licenseManager = StarterKitLicenseManager::validate($package, $this->option('license'), $this);

        if (! $licenseManager->isValid()) {
            return;
        }

        if ($cleared = $this->shouldClear()) {
            $this->call('statamic:site:clear', ['--no-interaction' => true]);
        }

        $installer = StarterKitInstaller::package($package, $licenseManager, $this)
            ->fromLocalRepo($this->option('local'))
            ->withConfig($this->option('with-config'))
            ->withoutDependencies($this->option('without-dependencies'))
            ->withUser($cleared && $this->input->isInteractive())
            ->force($this->option('force'));

        try {
            $installer->install();
        } catch (StarterKitException $exception) {
            $this->error($exception->getMessage());

            return 1;
        }

        $this->info("Starter kit [$package] was successfully installed.");
    }

    /**
     * Get composer package.
     *
     * @return string
     */
    protected function getPackage()
    {
        return $this->argument('package') ?: $this->ask('Package');
    }

    /**
     * Check if should clear site first.
     *
     * @return bool
     */
    protected function shouldClear()
    {
        if ($this->option('clear-site')) {
            return true;
        } elseif ($this->input->isInteractive()) {
            return $this->confirm('Clear site first?', false);
        }

        return false;
    }
}

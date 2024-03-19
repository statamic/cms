<?php

namespace Statamic\Console\Commands;

use Illuminate\Console\Command;
use Statamic\Console\RunsInPlease;
use Statamic\Console\ValidatesInput;
use Statamic\Rules\ComposerPackage;
use Statamic\StarterKits\Exceptions\StarterKitException;
use Statamic\StarterKits\Installer as StarterKitInstaller;

class StarterKitRunPostInstall extends Command
{
    use RunsInPlease, ValidatesInput;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'statamic:starter-kit:run-post-install { package : Specify the starter kit package }';

    /**
     * Indicates whether the command should be shown in the Artisan command list.
     *
     * @var bool
     */
    protected $hidden = true;

    /**
     * Execute the console command.
     */
    public function handle()
    {
        if ($this->validationFails($package = $this->argument('package'), new ComposerPackage)) {
            return;
        }

        if (! app('files')->exists(base_path("vendor/{$package}"))) {
            $this->error("Cannot find starter kit [{$package}] in vendor.");

            return 1;
        }

        $installer = StarterKitInstaller::package($package, $this);

        try {
            $installer->runPostInstallHook(true)->removeStarterKit();
        } catch (StarterKitException $exception) {
            $this->error($exception->getMessage());

            return 1;
        }

        $this->info("Starter kit [$package] was successfully installed.");
    }
}

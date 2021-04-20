<?php

namespace Statamic\Console\Commands;

use Facades\Statamic\StarterKits\Installer as StarterKitInstaller;
use Illuminate\Console\Command;
use Statamic\Console\RunsInPlease;
use Statamic\StarterKits\Exceptions\StarterKitException;

class StarterKitInstall extends Command
{
    use RunsInPlease;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'statamic:starter-kit:install
        { package : Specify the starter kit package to install }
        { --with-config : Copy starter-kit.yaml config for development }';

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
        $installer = StarterKitInstaller::withConfig($this->option('with-config'));

        try {
            $installer->install($package = $this->argument('package'), $this);
        } catch (StarterKitException $exception) {
            return $this->error($exception->getMessage());
        }

        $this->info("Starter kit [$package] was successfully installed.");
    }
}

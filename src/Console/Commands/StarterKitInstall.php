<?php

namespace Statamic\Console\Commands;

use Statamic\StarterKits\Installer as StarterKitInstaller;
use Illuminate\Console\Command;
use Statamic\Console\RunsInPlease;
use Statamic\Console\ValidatesInput;
use Statamic\Rules\ComposerPackage;
use Statamic\StarterKits\Exceptions\StarterKitException;

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
        { --with-config : Copy starter-kit.yaml config for development }
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
        if ($this->validationFails($package = $this->getPackage(), new ComposerPackage)) {
            return;
        }

        if ($this->shouldClear()) {
            $this->call('statamic:site:clear', ['--no-interaction' => true]);
        }

        $installer = (new StarterKitInstaller)
            ->withConfig($this->option('with-config'))
            ->withoutDependencies($this->option('without-dependencies'))
            ->force($this->option('force'));

        try {
            $installer->install($package, $this);
        } catch (StarterKitException $exception) {
            return $this->error($exception->getMessage());
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
        return $this->option('clear-site') ?: $this->confirm('Clear site first?', false);
    }
}

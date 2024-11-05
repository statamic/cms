<?php

namespace Statamic\Console\Commands;

use Illuminate\Console\Command;
use Statamic\Console\RunsInPlease;
use Statamic\Extend\Uninstaller;
use Statamic\Facades\Addon;

class AddonsUninstall extends Command
{
    use RunsInPlease;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'statamic:addons:uninstall package
        { package : The Composer package of the addon }';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Allows an addon to clean up before being uninstalled';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $addon = Addon::get($this->argument('package'));

        // This command will get called for every package regardless of whether
        // it's an addon or not. If it's not an addon, we'll just ignore it.
        if (! $addon) {
            return;
        }

        if (class_exists($class = $addon->namespace().'\\Uninstall')) {
            /** @var Uninstaller $class */
            $class = app($class);
            $class->setOutput($this->output)->setAddon($addon)->handle();
        }
    }
}

<?php

namespace Statamic\Console\Commands;

use Facades\Statamic\UpdateScripts\Manager as UpdateScriptManager;
use Illuminate\Console\Command;
use Statamic\Console\RunsInPlease;
use Statamic\Statamic;

class UpdatesRun extends Command
{
    use RunsInPlease;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'statamic:updates:run
        { version : Specify the version you are updating from }
        { --package= : Specify a specific package you are updating from (ie. john/my-addon) }';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Run update scripts from specific version';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $package = $this->option('package') ?? Statamic::PACKAGE;

        $success = UpdateScriptManager::runUpdatesForSpecificPackageVersion($package, $this->argument('version'), $this);

        $success
            ? $this->info('Update scripts were run successfully!')
            : $this->comment('There were no update scripts for this version.');
    }
}

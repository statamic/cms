<?php

namespace Statamic\Console\Commands;

use Illuminate\Console\Command;
use Statamic\Console\RunsInPlease;
use Statamic\Statamic;
use Statamic\UpdateScripts\UpdateScript;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

class UpdatesRun extends Command
{
    use RunsInPlease;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'statamic:updates:run';

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

        $success = UpdateScript::runAllFromSpecificPackageVersion($package, $this->argument('version'), $this);

        $success
            ? $this->info('Update scripts were run successfully!')
            : $this->comment('There were no update scripts for this version.');
    }

    /**
     * Get the console command arguments.
     *
     * @return array
     */
    protected function getArguments()
    {
        return [
            ['version', InputArgument::REQUIRED, 'Specify the version you are updating from'],
        ];
    }

    /**
     * Get the console command options.
     *
     * @return array
     */
    protected function getOptions()
    {
        return [
            ['package', '', InputOption::VALUE_REQUIRED, 'Specify a specific package you are updating from (ie. john/my-addon)'],
        ];
    }
}

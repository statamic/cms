<?php

namespace Statamic\Console\Commands;

use Facades\Statamic\Console\Processes\Composer;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Process;
use Statamic\Console\EnhancesCommands;
use Statamic\Console\RunsInPlease;
use Symfony\Component\Process\PhpExecutableFinder;

use function Laravel\Prompts\confirm;

class InstallSsg extends Command
{
    use EnhancesCommands, RunsInPlease;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'statamic:install:ssg';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = "Install & configure Statamic's Static Site Generator package";

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        if (Composer::isInstalled('statamic/ssg')) {
            return $this->error('The Static Site Generator package is already installed.');
        }

        $this->info('Installing the statamic/ssg package...');
        Composer::withoutQueue()->throwOnFailure()->require('statamic/ssg');
        $this->checkLine('Installed statamic/ssg package');

        if (confirm('Would you like to publish the config file?')) {
            Process::run([
                (new PhpExecutableFinder())->find(false) ?: 'php',
                defined('ARTISAN_BINARY') ? ARTISAN_BINARY : 'artisan',
                'vendor:publish',
                '--provider',
                'Statamic\\StaticSite\\ServiceProvider',
            ]);

            $this->checkLine('Config file published. You can find it at config/statamic/ssg.php');
        }

        if (
            ! Composer::isInstalled('spatie/fork')
            && confirm('Would you like to install spatie/fork? It allows for running multiple workers at once.')
        ) {
            Composer::withoutQueue()->throwOnFailure()->require('spatie/fork');
            $this->checkLine('Installed spatie/fork package');
        }
    }
}

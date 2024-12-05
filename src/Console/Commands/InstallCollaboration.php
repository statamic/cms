<?php

namespace Statamic\Console\Commands;

use Facades\Statamic\Console\Processes\Composer;
use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Process;
use Illuminate\Support\Str;
use Statamic\Console\EnhancesCommands;
use Statamic\Console\RunsInPlease;
use Symfony\Component\Process\PhpExecutableFinder;

use function Laravel\Prompts\select;
use function Laravel\Prompts\spin;

class InstallCollaboration extends Command
{
    use EnhancesCommands, RunsInPlease;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'statamic:install:collaboration';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Installs the Statamic Collaboration addon and enables broadcasting in Laravel.';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        if (! Composer::isInstalled('statamic/collaboration')) {
            spin(
                fn () => Composer::withoutQueue()->throwOnFailure()->require('statamic/collaboration', null, '--no-scripts'),
                'Installing the statamic/collaboration addon...'
            );

            $this->components->info('Installed statamic/collaboration addon');
        }

        $this->enableBroadcasting();
        $this->warnAboutLegacyBroadcastDriverKey();
        $this->installBroadcastingDriver();
    }

    protected function enableBroadcasting(): void
    {
        if (version_compare(app()->version(), '11', '<')) {
            $this->enableBroadcastServiceProvider();
            $this->components->info('Broadcasting enabled successfully.');

            return;
        }

        if (File::exists(config_path('broadcasting.php'))) {
            $this->components->warn('Broadcasting is already enabled.');

            return;
        }

        spin(
            callback: function () {
                Process::run([
                    (new PhpExecutableFinder())->find(false) ?: 'php',
                    defined('ARTISAN_BINARY') ? ARTISAN_BINARY : 'artisan',
                    'install:broadcasting',
                    '--without-reverb',
                    '--without-node',
                ]);
            },
            message: 'Enabling broadcasting...'
        );
        $this->components->info('Broadcasting enabled successfully.');
    }

    protected function warnAboutLegacyBroadcastDriverKey(): void
    {
        if (version_compare(app()->version(), '11', '<')) {
            return;
        }

        if (Str::contains(File::get(app()->environmentFile()), 'BROADCAST_DRIVER')) {
            $this->components->warn('The BROADCAST_DRIVER environment variable has been renamed to BROADCAST_CONNECTION in Laravel 11. You should update your .env file.');
        }
    }

    protected function installBroadcastingDriver(): void
    {
        $driver = select(
            label: 'Which broadcasting driver would you like to use?',
            options: ['Laravel Reverb', 'Pusher', 'Other'],
        );

        if ($driver === 'Laravel Reverb') {
            spin(
                callback: function () {
                    Composer::withoutQueue()->throwOnFailure()->require('laravel/reverb', '@beta');

                    Process::run([
                        (new PhpExecutableFinder())->find(false) ?: 'php',
                        defined('ARTISAN_BINARY') ? ARTISAN_BINARY : 'artisan',
                        'reverb:install',
                    ]);
                },
                message: 'Installing Laravel Reverb...'
            );

            $this->components->info('Laravel Reverb installed successfully.');
        }

        if ($driver === 'Pusher') {
            spin(
                callback: function () {
                    Composer::withoutQueue()->throwOnFailure()->require('pusher/pusher-php-server');

                    $this->addPusherEnvironmentVariables();
                    $this->updateBroadcastingDriver('pusher');
                },
                message: 'Installing Pusher...'
            );

            $this->components->info("Pusher installed successfully. Don't forget to add your Pusher credentials to your .env file.");
        }

        if ($driver === 'Other') {
            $this->components->warn("You'll need to install and configure your own broadcasting driver.");
        }
    }

    /**
     * Uncomment the "BroadcastServiceProvider" in the application configuration.
     * Copied from Laravel's BroadcastingInstallCommand to support Laravel 10 applications.
     *
     * @return void
     */
    protected function enableBroadcastServiceProvider()
    {
        $config = ($filesystem = new Filesystem)->get(app()->configPath('app.php'));

        if (str_contains($config, '// App\Providers\BroadcastServiceProvider::class')) {
            $filesystem->replaceInFile(
                '// App\Providers\BroadcastServiceProvider::class',
                'App\Providers\BroadcastServiceProvider::class',
                app()->configPath('app.php'),
            );
        }
    }

    protected function updateBroadcastingDriver(string $driver): void
    {
        if (File::missing($env = app()->environmentFile())) {
            return;
        }

        File::put(
            $env,
            Str::of(File::get($env))->replaceMatches('/(BROADCAST_(?:DRIVER|CONNECTION))=\w*/', function (array $matches) use ($driver) {
                return $matches[1].'='.$driver;
            })
        );
    }

    /**
     * Add the Pusher variables to the environment file.
     */
    protected function addPusherEnvironmentVariables(): void
    {
        if (File::missing($env = app()->environmentFile())) {
            return;
        }

        $contents = File::get($env);

        $variables = Arr::where([
            'PUSHER_APP_ID' => 'PUSHER_APP_ID=',
            'PUSHER_APP_KEY' => 'PUSHER_APP_KEY=',
            'PUSHER_APP_SECRET' => 'PUSHER_APP_SECRET=',
            'PUSHER_HOST' => 'PUSHER_HOST=',
            'PUSHER_PORT' => 'PUSHER_PORT=443',
            'PUSHER_SCHEME' => 'PUSHER_SCHEME=https',
            'PUSHER_APP_CLUSTER' => 'PUSHER_APP_CLUSTER=mt1',
            'PUSHER_NEW_LINE' => null,
            'VITE_PUSHER_APP_KEY' => 'VITE_PUSHER_APP_KEY="${PUSHER_APP_KEY}"',
            'VITE_PUSHER_HOST' => 'VITE_PUSHER_HOST="${PUSHER_HOST}"',
            'VITE_PUSHER_PORT' => 'VITE_PUSHER_PORT="${PUSHER_PORT}"',
            'VITE_PUSHER_SCHEME' => 'VITE_PUSHER_SCHEME="${PUSHER_SCHEME}"',
            'VITE_PUSHER_APP_CLUSTER' => 'VITE_REVERB_APP_CLUSTER="${PUSHER_APP_CLUSTER}"',
        ], function ($value, $key) use ($contents) {
            return ! Str::contains($contents, PHP_EOL.$key);
        });

        $variables = trim(implode(PHP_EOL, $variables));

        if ($variables === '') {
            return;
        }

        File::append(
            $env,
            Str::endsWith($contents, PHP_EOL) ? PHP_EOL.$variables.PHP_EOL : PHP_EOL.PHP_EOL.$variables.PHP_EOL,
        );
    }
}

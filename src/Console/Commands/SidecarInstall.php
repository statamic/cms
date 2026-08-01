<?php

namespace Statamic\Console\Commands;

use Facades\Statamic\Console\Processes\Composer;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Statamic\Console\EnhancesCommands;
use Statamic\Console\RunsInPlease;
use Statamic\Facades\Sidecar;
use Statamic\Support\Str;

use function Laravel\Prompts\confirm;
use function Laravel\Prompts\error;
use function Laravel\Prompts\info;
use function Laravel\Prompts\select;
use function Laravel\Prompts\spin;

class SidecarInstall extends Command
{
    use EnhancesCommands, RunsInPlease;

    protected $signature = 'statamic:sidecar:install {driver? : The Sidecar driver to install}';

    protected $description = 'Install & configure a Sidecar collection adapter driver';

    public function handle()
    {
        $driver = $this->argument('driver') ?? $this->resolveDriver();

        if (! $driver) {
            return;
        }

        $package = $this->packageForDriver($driver);

        if ($package && ! Composer::isInstalled($package)) {
            spin(
                fn () => Composer::withoutQueue()->throwOnFailure()->require($package),
                "Installing {$package}..."
            );

            $this->checkLine("Installed {$package}");
        } elseif ($package) {
            $this->checkLine("{$package} is already installed");
        }

        $this->writeConfig($driver);

        info('Sidecar is ready. Your adapted collections will appear in the Control Panel.');
    }

    protected function resolveDriver(): ?string
    {
        $detected = $this->detectDrivers();

        if ($detected->isEmpty()) {
            $available = collect(Sidecar::registeredDrivers());

            if ($available->isEmpty() && Sidecar::packages()->isEmpty()) {
                error('No Sidecar drivers are available. Install a driver package first (e.g. composer require statamic/sidecar-laradocs), then re-run this command.');

                return null;
            }

            $choices = Sidecar::packages()
                ->mapWithKeys(fn ($package, $ssg) => [Str::afterLast($package, '/sidecar-') => "{$package} (for {$ssg})"])
                ->merge($available->mapWithKeys(fn ($driver) => [$driver => $driver]))
                ->all();

            return select('Which Sidecar driver would you like to install?', $choices);
        }

        if ($detected->count() === 1) {
            $driver = $detected->keys()->first();

            if (confirm("Detected {$detected->first()}. Install the [{$driver}] Sidecar driver?")) {
                return $driver;
            }

            return null;
        }

        return select(
            'Multiple compatible packages detected. Which Sidecar driver would you like to install?',
            $detected->mapWithKeys(fn ($ssg, $driver) => [$driver => "{$driver} ({$ssg})"])->all()
        );
    }

    protected function detectDrivers()
    {
        return Sidecar::packages()
            ->filter(fn ($package, $ssg) => Composer::isInstalled($ssg))
            ->mapWithKeys(fn ($package, $ssg) => [Str::afterLast($package, '/sidecar-') => $ssg]);
    }

    protected function packageForDriver(string $driver): ?string
    {
        $package = Sidecar::packages()->first(
            fn ($package) => Str::endsWith($package, '/sidecar-'.$driver) || Str::endsWith($package, '/'.$driver)
        );

        if ($package) {
            return $package;
        }

        // Driver packages register via Sidecar::pair(), so when the package
        // isn't installed yet we fall back to the first-party naming convention.
        if (! Sidecar::hasDriver($driver)) {
            return 'statamic/sidecar-'.$driver;
        }

        return null;
    }

    protected function writeConfig(string $driver): void
    {
        $path = config_path('statamic/sidecar.php');

        if (! File::exists($path)) {
            File::ensureDirectoryExists(dirname($path));
            File::copy(__DIR__.'/../../../config/sidecar.php', $path);
            $this->checkLine('Published config/statamic/sidecar.php');
        }

        $config = require $path;

        if (isset($config['collections'][$this->defaultHandleFor($driver)])) {
            $this->checkLine('Sidecar collection config already present');

            return;
        }

        if (! confirm('Would you like to add a default collection config for this driver?')) {
            return;
        }

        $handle = $this->defaultHandleFor($driver);
        $directory = $this->defaultDirectoryFor($driver);

        $stub = File::get($path);

        $entry = <<<PHP

        '{$handle}' => [
            'driver' => '{$driver}',
            'directory' => {$directory},
        ],
PHP;

        if (Str::contains($stub, "'collections' => [")) {
            $stub = Str::replaceFirst(
                "'collections' => [",
                "'collections' => [".$entry,
                $stub
            );

            // Remove the example comment block if present to keep the file tidy.
            $stub = preg_replace('/\n\s*\/\/ \'docs\' => \[.*?\],\n/s', "\n", $stub);

            File::put($path, $stub);
            $this->checkLine("Added [{$handle}] collection to config/statamic/sidecar.php");
        } else {
            error('Could not automatically update config/statamic/sidecar.php. Please add the collection manually.');
        }
    }

    protected function defaultHandleFor(string $driver): string
    {
        return match ($driver) {
            'laradocs' => 'docs',
            default => $driver,
        };
    }

    protected function defaultDirectoryFor(string $driver): string
    {
        return match ($driver) {
            'laradocs' => "base_path('docs')",
            default => "base_path('{$driver}')",
        };
    }
}

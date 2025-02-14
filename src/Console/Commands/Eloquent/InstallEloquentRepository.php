<?php

namespace Statamic\Console\Commands\Eloquent;

use Illuminate\Console\Command;
use function Laravel\Prompts\confirm;
use Statamic\Console\Commands\Concerns\RunsArtisanCommand;
use Statamic\Console\EnhancesCommands;
use Statamic\Console\RunsInPlease;
use Statamic\Facades\File;
use Statamic\Support\Str;

abstract class InstallEloquentRepository extends Command
{
    use EnhancesCommands, RunsInPlease, RunsArtisanCommand;

    abstract public function hasBeenMigrated(): bool;

    public function repoHandle(): string
    {
        return $this->handle;
    }

    public function repoTitle(): string
    {
        return $this->title ?? Str::of($this->repoHandle())->replace('_', ' ')->title();
    }

    protected function switchToEloquentDriver(): void
    {
        File::put(
            config_path('statamic/eloquent-driver.php'),
            Str::of(File::get(config_path('statamic/eloquent-driver.php')))
                ->replace(
                    "'{$this->repoHandle()}' => [\n        'driver' => 'file'",
                    "'{$this->repoHandle()}' => [\n        'driver' => 'eloquent'"
                )
                ->__toString()
        );
    }

    protected function shouldImport(string $repository): bool
    {
        return $this->option('import') || confirm("Would you like to import existing {$repository}?");
    }

    protected function infoMessage(string $message): void
    {
        if ($this->option('without-messages')) {
            return;
        }

        $this->components->info($message);
    }
}

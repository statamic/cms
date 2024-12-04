<?php

namespace Statamic\Console\Commands;

use Illuminate\Console\Command;
use Statamic\Console\RunsInPlease;
use Statamic\Facades\File;
use Statamic\Facades\Path;
use Statamic\StarterKits\Exceptions\StarterKitException;
use Statamic\StarterKits\Exporter as StarterKitExporter;

use function Laravel\Prompts\confirm;

class StarterKitExport extends Command
{
    use RunsInPlease;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'statamic:starter-kit:export
        { path : Specify the path you are exporting to }
        { --clear : Clear out everything at target export path before exporting }';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Export to starter kit';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        if ($this->isUsingLegacyExporterConventions()) {
            $this->askToMigrateToPackageFolder();
        }

        if (! File::exists($path = $this->getAbsolutePath())) {
            $this->askToCreateExportPath($path);
        }

        $exporter = (new StarterKitExporter($path))
            ->clear($this->option('clear'));

        try {
            $exporter->export();
        } catch (StarterKitException $exception) {
            $this->components->error($exception->getMessage());

            return 1;
        }

        $this->components->info("Starter kit was successfully exported to [$path].");
    }

    /**
     * Get absolute path.
     */
    protected function getAbsolutePath(): string
    {
        $path = $this->argument('path');

        return Path::isAbsolute($path)
            ? $path
            : Path::resolve(Path::makeFull($path));
    }

    /**
     * Ask to create export path.
     */
    protected function askToCreateExportPath(string $path): void
    {
        if ($this->input->isInteractive()) {
            if (! confirm("Path [{$path}] does not exist. Would you like to create it now?", true)) {
                return;
            }
        }

        File::makeDirectory($path, 0755, true);

        $this->components->info("A new directory has been created at [{$path}].");
    }

    /**
     * Determine if dev sandbox has starter-kit.yaml at root and/or customized composer.json at target path.
     */
    protected function isUsingLegacyExporterConventions(): bool
    {
        return File::exists(base_path('starter-kit.yaml'));
    }

    /**
     * Determine if dev sandbox has starter-kit.yaml at root and/or customized composer.json at target path.
     */
    protected function askToMigrateToPackageFolder(): void
    {
        if ($this->input->isInteractive()) {
            if (! confirm('Config should now live in the [package] folder. Would you like Statamic to move it for you?', true)) {
                return;
            }
        }

        if (! File::exists($dir = base_path('package'))) {
            File::makeDirectory($dir, 0755, true);
        }

        if (File::exists($starterKitConfig = base_path('starter-kit.yaml'))) {
            File::move($starterKitConfig, base_path('package/starter-kit.yaml'));
            $this->components->info('Starter kit config moved to [package/starter-kit.yaml].');
        }

        if (File::exists($postInstallHook = base_path('StarterKitPostInstall.php'))) {
            File::move($postInstallHook, base_path('package/StarterKitPostInstall.php'));
            $this->components->info('Starter kit post-install hook moved to [package/StarterKitPostInstall.php].');
        }

        if (File::exists($packageComposerJson = $this->getAbsolutePath().'/composer.json')) {
            File::move($packageComposerJson, base_path('package/composer.json'));
            $this->components->info('Composer package config moved to [package/composer.json].');
        }
    }
}

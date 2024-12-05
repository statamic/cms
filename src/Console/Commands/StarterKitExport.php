<?php

namespace Statamic\Console\Commands;

use Illuminate\Console\Command;
use Statamic\Console\Commands\Concerns\MigratesLegacyStarterKitConfig;
use Statamic\Console\RunsInPlease;
use Statamic\Facades\File;
use Statamic\Facades\Path;
use Statamic\StarterKits\Exceptions\StarterKitException;
use Statamic\StarterKits\Exporter as StarterKitExporter;

use function Laravel\Prompts\confirm;

class StarterKitExport extends Command
{
    use MigratesLegacyStarterKitConfig, RunsInPlease;

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
        $path = $this->getAbsolutePath();

        $this->migrateLegacyConfig($path);

        if (! File::exists($path)) {
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

        if (version_compare(app()->version(), '11', '<')) {
            return $this->components->info("Starter kit was successfully exported to [$path].");
        }

        $this->components->success("Starter kit was successfully exported to [$path].");
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
}

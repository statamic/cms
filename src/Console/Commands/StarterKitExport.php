<?php

namespace Statamic\Console\Commands;

use Facades\Statamic\StarterKits\Exporter as StarterKitExporter;
use Illuminate\Console\Command;
use Statamic\Console\RunsInPlease;
use Statamic\Facades\File;
use Statamic\Facades\Path;
use Statamic\StarterKits\Exceptions\StarterKitException;

class StarterKitExport extends Command
{
    use RunsInPlease;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'statamic:starter-kit:export { path : Specify the path you are exporting to }';

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
        if (! File::exists(base_path('starter-kit.yaml'))) {
            return $this->askToStubStarterKitConfig();
        }

        if (! File::exists($path = $this->getAbsolutePath())) {
            $this->askToCreateExportPath($path);
        }

        try {
            StarterKitExporter::export($path);
        } catch (StarterKitException $exception) {
            $this->error($exception->getMessage());

            return 1;
        }

        $this->info("Starter kit was successfully exported to [$path].");
    }

    /**
     * Ask to stub out starter kit config.
     */
    protected function askToStubStarterKitConfig()
    {
        $stubPath = __DIR__.'/stubs/starter-kits/starter-kit.yaml.stub';
        $newPath = base_path($config = 'starter-kit.yaml');

        if ($this->input->isInteractive()) {
            if (! $this->confirm("Config [{$config}] does not exist. Would you like to create it now?", true)) {
                return;
            }
        }

        File::copy($stubPath, $newPath);

        $this->comment("A new config has been created at [{$config}].");
        $this->comment('Please configure your `export_paths` and re-run to begin your export!');
    }

    /**
     * Get absolute path.
     *
     * @return string
     */
    protected function getAbsolutePath()
    {
        $path = $this->argument('path');

        return Path::isAbsolute($path)
            ? $path
            : Path::resolve(Path::makeFull($path));
    }

    /**
     * Ask to create export path.
     *
     * @param  string  $path
     */
    protected function askToCreateExportPath($path)
    {
        if ($this->input->isInteractive()) {
            if (! $this->confirm("Path [{$path}] does not exist. Would you like to create it now?", true)) {
                return;
            }
        }

        File::makeDirectory($path, 0755, true);

        $this->comment("A new directory has been created at [{$path}].");
    }
}

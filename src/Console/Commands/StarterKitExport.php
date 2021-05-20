<?php

namespace Statamic\Console\Commands;

use Facades\Statamic\StarterKits\Exporter as StarterKitExporter;
use Illuminate\Console\Command;
use Statamic\Console\RunsInPlease;
use Statamic\Facades\File;
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
            return $this->stubStarterKitConfig();
        }

        try {
            StarterKitExporter::export($path = $this->argument('path'));
        } catch (StarterKitException $exception) {
            $this->error($exception->getMessage());

            return 1;
        }

        $this->info("Starter kit was successfully exported to [$path].");
    }

    /**
     * Stub out starter kit config.
     */
    protected function stubStarterKitConfig()
    {
        $stubPath = __DIR__.'/stubs/starter-kits/starter-kit.yaml.stub';
        $newPath = base_path('starter-kit.yaml');

        File::copy($stubPath, $newPath);

        $this->comment('A new config has been created at [starter-kit.yaml].');
        $this->comment('Please configure your `export_paths` and re-run to begin your export!');
    }
}

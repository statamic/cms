<?php

namespace Statamic\Console\Commands;

use Illuminate\Console\Command;
use Statamic\Console\RunsInPlease;
use Statamic\Facades\File;
use Statamic\Statamic;

class Install extends Command
{
    use RunsInPlease;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'statamic:install';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Install Statamic';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $this->addons()
             ->createFiles()
             ->publish()
             ->runCallbacks()
             ->clearViews()
             ->clearCache();
    }

    protected function addons()
    {
        $this->call('statamic:addons:discover');

        return $this;
    }

    protected function createFiles()
    {
        $gitkeeps = [
            base_path('content/assets'),
            base_path('content/collections'),
            base_path('content/globals'),
            base_path('content/taxonomies'),
            base_path('content/navigation'),
            base_path('users'),
        ];

        $gitignores = [
            storage_path('statamic'),
        ];

        foreach ($gitkeeps as $dir) {
            if (! File::exists($gitkeep = $dir.'/.gitkeep')) {
                File::put($gitkeep, '');
                $this->info("Created the <comment>[$dir]</comment> directory.");
            }
        }

        foreach ($gitignores as $dir) {
            if (! File::exists($gitignore = $dir.'/.gitignore')) {
                File::put($gitignore, "*\n!.gitignore");
                $this->info("Created the <comment>[$dir]</comment> directory.");
            }
        }

        return $this;
    }

    protected function publish()
    {
        $this->call('vendor:publish', ['--tag' => 'statamic']);
        $this->call('vendor:publish', ['--tag' => 'statamic-cp', '--force' => true]);

        return $this;
    }

    protected function clearViews()
    {
        $this->call('view:clear');

        return $this;
    }

    protected function clearCache()
    {
        $this->call('cache:clear');

        return $this;
    }

    protected function runCallbacks()
    {
        Statamic::runAfterInstalledCallbacks($this);

        return $this;
    }
}

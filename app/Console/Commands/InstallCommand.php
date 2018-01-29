<?php

namespace Statamic\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;

class InstallCommand extends Command
{
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

    protected $files;

    public function __construct(Filesystem $files)
    {
        parent::__construct();

        $this->files = $files;
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $this->addons()
             ->createFiles()
             ->publish();
    }

    protected function addons()
    {
        $this->call('package:discover-addons');

        return $this;
    }

    protected function createFiles()
    {
        $dirs = [
            base_path('content/pages'),
            base_path('content/collections'),
            base_path('content/taxonomies'),
            base_path('users'),
        ];

        foreach ($dirs as $dir) {
            if (! $this->files->exists($dir)) {
                $this->files->makeDirectory($dir, 0777, true);
                $this->info("Created the <comment>[$dir]</comment> directory.");
            }
        }

        return $this;
    }

    protected function publish()
    {
        $this->call('vendor:publish', ['--tag' => 'statamic']);

        return $this;
    }
}

<?php

namespace Statamic\Console\Commands;

use Illuminate\Console\Command;
use Statamic\Console\RunsInPlease;
use Illuminate\Filesystem\Filesystem;

class SiteClear extends Command
{
    use RunsInPlease;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'statamic:site:clear';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Start a fresh site, wiping away all content';

    /**
     * Filesystem.
     *
     * @var Filesystem
     */
    protected $files;

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $this->files = app(Filesystem::class);

        $this
            ->clearCollections()
            ->clearStructures()
            ->clearAssets()
            ->clearViews();
    }

    /**
     * Clear all collections.
     *
     * @return $this
     */
    public function clearCollections()
    {
        $this->files->cleanDirectory(base_path('content/collections'));

        $this->info('Collections cleared successfully.');

        return $this;
    }

    /**
     * Clear all structures.
     *
     * @return $this
     */
    public function clearStructures()
    {
        $this->files->cleanDirectory(base_path('content/structures'));

        $this->info('Structures cleared successfully.');

        return $this;
    }

    /**
     * Clear all assets.
     *
     * @return $this
     */
    public function clearAssets()
    {
        $this->files->cleanDirectory(base_path('content/assets'));

        $this->info('Assets cleared successfully.');

        return $this;
    }

    /**
     * Clear all views.
     *
     * @return $this
     */
    public function clearViews()
    {
        $this->files->cleanDirectory(resource_path('views'));

        $this->info('Views cleared successfully.');

        return $this;
    }
}

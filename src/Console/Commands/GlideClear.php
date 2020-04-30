<?php

namespace Statamic\Console\Commands;

use Facades\Statamic\Imaging\GlideServer;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Statamic\Console\RunsInPlease;
use Statamic\Facades\File;
use Statamic\Facades\Folder;

class GlideClear extends Command
{
    use RunsInPlease;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'statamic:glide:clear';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clear the Glide image cache';

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle()
    {
        // Get the glide server cache path.
        $cachePath = GlideServer::cachePath();

        // Delete the cached images.
        collect(Folder::getFilesRecursively($cachePath))->each(function ($path) {
            File::delete($path);
        });

        // Clean up subfolders.
        Folder::deleteEmptySubfolders($cachePath);

        // Remove the cached keys so the middleware doesn't try to load a non existent image.
        collect(Cache::get('glide::paths', []))->keys()->each(function ($key) {
            Cache::forget("glide::paths.$key");
        });

        $this->info('Your Glide image cache is now so very, very empty.');
    }
}

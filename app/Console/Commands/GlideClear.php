<?php

namespace Statamic\Console\Commands;

use Statamic\API\File;
use Statamic\API\Cache;
use Statamic\API\Folder;
use Illuminate\Console\Command;

class GlideClear extends Command
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'statamic:glide:clear';

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
    public function fire()
    {
        // Delete the cached images
        collect(Folder::disk('glide')->getFilesRecursively('/'))->each(function ($path) {
            File::disk('glide')->delete($path);
        });

        // Clean up subfolders
        Folder::disk('glide')->deleteEmptySubfolders('/');

        // Remove the cached keys so the middleware doesn't try to load a non existent image.
        collect(Cache::get('glide::paths', []))->keys()->each(function ($key) {
            Cache::forget("glide::paths.$key");
        });

        $this->info('Your Glide image cache is now so very, very empty.');
    }
}

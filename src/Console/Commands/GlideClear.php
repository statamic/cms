<?php

namespace Statamic\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use League\Glide\Server;
use Statamic\Console\RunsInPlease;

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
        // Get the glide server cache file system.
        $filesystem = app(Server::class)->getCache();

        // Delete the cached images.
        collect($filesystem->listContents())->each(function ($item) use ($filesystem) {
            if ($item['type'] === 'dir') {
                $filesystem->deleteDir($item['path']);
            } else {
                $filesystem->delete($item['path']);
            }
        });

        // Remove the cached keys so the middleware doesn't try to load a non existent image.
        collect(Cache::get('glide::paths', []))->keys()->each(function ($key) {
            Cache::forget("glide::paths.$key");
        });

        $this->info('Your Glide image cache is now so very, very empty.');
    }
}

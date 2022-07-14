<?php

namespace Statamic\Console\Commands;

use Illuminate\Console\Command;
use Statamic\Console\RunsInPlease;
use Statamic\Facades\File;
use Statamic\Facades\Glide;
use Statamic\Filesystem\Filesystem;
use Statamic\Support\FileCollection;

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
        $this->clearCache();

        $disk = File::disk(Glide::cacheDisk());

        $files = $this->getFiles($disk);

        if (! $files->isEmpty()) {
            $this->deleteImages($disk, $files);
        }

        $this->deleteEmptyDirectories($disk);

        $this->info('Your Glide image cache is now so very, very empty.');
    }

    private function clearCache()
    {
        Glide::cacheStore()->flush();
        $this->line('<info>[✔]</info> Glide path cache cleared.');
    }

    private function getFiles(Filesystem $disk)
    {
        $this->line('Counting images to delete...');

        $files = $disk->getFilesRecursively('/');

        $this->line("\x1B[1A\x1B[2K<info>[✔]</info> Found {$files->count()} images.");

        return $files;
    }

    private function deleteImages(Filesystem $disk, FileCollection $files)
    {
        $bar = $this->output->createProgressBar($files->count());
        $bar->setFormat('[%current%/%max%] Deleting <comment>%path%</comment>...');

        $files->each(function ($path) use ($disk, $bar) {
            $bar->setMessage($path, 'path');
            $disk->delete($path);
            $bar->advance();
        });

        $bar->setFormat('<info>[✔]</info> Images deleted.');
        $bar->finish();
        $this->line('');
    }

    private function deleteEmptyDirectories(Filesystem $disk)
    {
        $this->line('Removing empty directories...');
        $disk->deleteEmptySubfolders('/');
        $this->line("\x1B[1A\x1B[2K<info>[✔]</info> Deleted empty directories.");
    }
}

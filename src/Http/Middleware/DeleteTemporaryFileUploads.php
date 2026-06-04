<?php

namespace Statamic\Http\Middleware;

use Closure;
use Statamic\Assets\ChunkUploads;
use Statamic\Facades\File;

class DeleteTemporaryFileUploads
{
    public function handle($request, Closure $next)
    {
        $this->deleteFilesOverAnHourOld();
        $this->deleteAbandonedChunks();
        $lottery = [2, 100];

        if (random_int(1, $lottery[1]) <= $lottery[0]) {
            $this->deleteFilesOverAnHourOld();
            $this->deleteAbandonedChunks();
        }

        return $next($request);
    }

    private function deleteFilesOverAnHourOld()
    {
        $disk = File::disk('local');

        $disk
            ->getFilesRecursively($dir = 'statamic/file-uploads')
            ->filter(function ($path) {
                $bits = explode('/', $path);
                $timestamp = $bits[count($bits) - 2];

                return $timestamp < now()->subHour()->timestamp;
            })
            ->each(fn ($path) => $disk->delete($path));

        $disk->deleteEmptySubfolders($dir);
    }

    private function deleteAbandonedChunks()
    {
        $disk = ChunkUploads::disk();

        // Each upload is a folder of chunks; delete it once nothing has been written to it for an hour.
        foreach ($disk->directories(ChunkUploads::baseDirectory()) as $folder) {
            $files = $disk->allFiles($folder);

            if ($files && max(array_map(fn ($path) => $disk->lastModified($path), $files)) < now()->subHour()->timestamp) {
                $disk->deleteDirectory($folder);
            }
        }
    }
}

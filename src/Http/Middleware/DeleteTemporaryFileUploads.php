<?php

namespace Statamic\Http\Middleware;

use Closure;
use Statamic\Assets\ChunkUploads;
use Statamic\Facades\File;

class DeleteTemporaryFileUploads
{
    public function handle($request, Closure $next)
    {
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
        $disk = File::disk(ChunkUploads::diskName());

        $disk
            ->getFilesRecursively($dir = ChunkUploads::baseDirectory())
            ->filter(fn ($path) => str_ends_with($path, '/.meta'))
            ->filter(fn ($path) => (int) $disk->get($path) < now()->subDay()->timestamp)
            ->each(fn ($meta) => $disk->getFilesRecursively(dirname($meta))->each(fn ($path) => $disk->delete($path)));

        $disk->deleteEmptySubfolders($dir);
    }
}

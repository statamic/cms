<?php

namespace Statamic\Http\Middleware;

use Closure;
use Statamic\Facades\File;

class DeleteTemporaryFileUploads
{
    public function handle($request, Closure $next)
    {
        $lottery = [2, 100];

        if (random_int(1, $lottery[1]) <= $lottery[0]) {
            $this->deleteFilesOverAnHourOld();
        }

        return $next($request);
    }

    private function deleteFilesOverAnHourOld()
    {
        $disk = File::disk();

        $dir = storage_path('statamic/file-uploads');

        $disk
            ->getFilesRecursively($dir)
            ->filter(function ($path) {
                $bits = explode('/', $path);
                $timestamp = $bits[count($bits) - 2];

                return $timestamp < now()->subHour()->timestamp;
            })
            ->each(fn ($path) => $disk->delete($path));

        $disk->deleteEmptySubfolders($dir);
    }
}

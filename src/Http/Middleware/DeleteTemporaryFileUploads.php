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
            $this->deleteFileUploadsOverAnHourOld();
            $this->deleteFormUploadsOverAWeekOld();
        }

        return $next($request);
    }

    private function deleteFileUploadsOverAnHourOld(): void
    {
        $disk = File::disk(config('statamic.system.file_uploads_disk', 'local'));
        $directory = config('statamic.system.file_uploads_path', 'statamic/file-uploads');

        $disk
            ->getFilesRecursively($directory)
            ->filter(function ($path) {
                $bits = explode('/', $path);
                $timestamp = $bits[count($bits) - 2];

                return $timestamp < now()->subHour()->timestamp;
            })
            ->each(fn ($path) => $disk->delete($path));

        $disk->deleteEmptySubfolders($directory);
    }

    private function deleteFormUploadsOverAWeekOld(): void
    {
        $disk = File::disk(config('statamic.system.file_uploads_disk', 'local'));
        $directory = config('statamic.forms.file_uploads_path', 'statamic/form-uploads');

        $disk
            ->getFilesRecursively($directory)
            ->filter(fn ($path) => $disk->lastModified($path) < now()->subWeek()->timestamp)
            ->each(fn ($path) => $disk->delete($path));

        $disk->deleteEmptySubfolders($directory);
    }
}

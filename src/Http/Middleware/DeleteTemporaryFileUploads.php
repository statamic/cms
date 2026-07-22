<?php

namespace Statamic\Http\Middleware;

use Carbon\CarbonInterface;
use Closure;
use Statamic\Facades\File;

class DeleteTemporaryFileUploads
{
    public function handle($request, Closure $next)
    {
        $lottery = [2, 100];

        if (random_int(1, $lottery[1]) <= $lottery[0]) {
            $this->deleteTemporaryFileUploads(
                directory: config('statamic.system.file_uploads_path', 'statamic/file-uploads'),
                olderThan: now()->subHour()
            );

            $this->deleteTemporaryFileUploads(
                directory: config('statamic.forms.file_uploads_path', 'statamic/form-uploads'),
                olderThan: now()->subWeek()
            );
        }

        return $next($request);
    }

    private function deleteTemporaryFileUploads(string $directory, CarbonInterface $olderThan): void
    {
        $disk = File::disk(config('statamic.system.file_uploads_disk', 'local'));

        $disk
            ->getFilesRecursively($directory)
            ->filter(function ($path) use ($olderThan) {
                $bits = explode('/', $path);
                $timestamp = $bits[count($bits) - 2];

                return $timestamp < $olderThan->timestamp;
            })
            ->each(fn ($path) => $disk->delete($path));

        $disk->deleteEmptySubfolders($directory);
    }
}

<?php

namespace Statamic\Http\Middleware;

use Closure;
use Statamic\Facades\File;
use Statamic\Facades\FormSubmission;
use Statamic\Support\Str;

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

        $submissionId = fn ($path) => explode('/', trim(Str::after($path, $directory), '/'))[0];

        $files = $disk
            ->getFilesRecursively($directory)
            ->filter(fn ($path) => $disk->lastModified($path) < now()->subWeek()->timestamp);

        $spamIds = $files->isEmpty() ? [] : FormSubmission::query()
            ->whereIn('id', $files->map($submissionId)->unique()->values()->all())
            ->whereStatus('spam')
            ->get()
            ->map->id()
            ->all();

        $files
            ->reject(fn ($path) => in_array($submissionId($path), $spamIds))
            ->each(fn ($path) => $disk->delete($path));

        $disk->deleteEmptySubfolders($directory);
    }
}

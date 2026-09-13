<?php

namespace Statamic\Assets;

use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Support\Facades\Storage;
use Statamic\Contracts\Assets\AssetContainer;
use Statamic\Support\Str;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class ChunkUploads
{
    public static function enabled(): bool
    {
        return (bool) config('statamic.assets.chunked_upload.enabled', true);
    }

    public static function enabledForContainer(?AssetContainer $container): bool
    {
        return self::enabled() && (bool) $container?->chunkedUploads();
    }

    public static function phpMaxUploadSize(): int
    {
        return (int) UploadedFile::getMaxFilesize();
    }

    public static function chunkSize(): int
    {
        $overhead = (int) config('statamic.assets.chunked_upload.chunk_overhead', 64 * 1024);
        $max = (int) config('statamic.assets.chunked_upload.max_chunk_size', 10 * 1024 * 1024);

        return max(1, min(self::phpMaxUploadSize() - $overhead, $max));
    }

    /**
     * The total upload size limit in bytes, read from the existing max file size
     * validation rules. Returns null when no size limit has been configured.
     */
    public static function maxFilesizeBytes(?array $rules): ?int
    {
        $kilobytes = collect($rules)
            ->filter(fn ($rule) => is_string($rule))
            ->map(function ($rule) {
                if (Str::startsWith($rule, 'max_filesize:')) {
                    return (int) Str::after($rule, ':');
                }

                if (Str::startsWith($rule, 'max:')) {
                    return (int) Str::after($rule, ':');
                }

                return null;
            })
            ->filter()
            ->min();

        return $kilobytes ? $kilobytes * 1024 : null;
    }

    public static function diskName(): string
    {
        return config('statamic.assets.chunked_upload.disk', 'local');
    }

    public static function disk(): Filesystem
    {
        return Storage::disk(self::diskName());
    }

    public static function baseDirectory(): string
    {
        return config('statamic.assets.chunked_upload.directory', 'statamic/chunks');
    }

    public static function directory(string $uploadId): string
    {
        return self::baseDirectory().'/'.$uploadId;
    }
}

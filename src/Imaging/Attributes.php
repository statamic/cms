<?php

namespace Statamic\Imaging;

use GuzzleHttp\Psr7\StreamWrapper;
use Illuminate\Filesystem\AwsS3V3Adapter;
use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Support\Facades\Storage;
use League\Flysystem\Local\LocalFilesystemAdapter;
use League\Flysystem\MountManager;
use Statamic\Assets\S3MetadataStream;
use Statamic\Support\Str;

class Attributes
{
    private const HEADER_WINDOW = 64 * 1024;
    private const HEADER_LIMIT = 2 * 1024 * 1024;
    private const S3_CHUNK_SIZE = 256 * 1024;

    private $cacheDisk;

    public function from(FilesystemAdapter $source, string $path)
    {
        $svg = Str::endsWith($path, '.svg');
        $local = $source->getAdapter() instanceof LocalFilesystemAdapter;

        if (! $local && ! $svg && ($attributes = $this->imageAttributesFromHeader($source, $path))) {
            return $attributes;
        }

        if ($local) {
            $fullPath = $source->path($path);
        } else {
            $temporaryPath = Str::random(32).'.'.pathinfo($path, PATHINFO_EXTENSION);

            $manager = $this->mountManager($source->getDriver(), $this->cacheDisk()->getDriver());
            $manager->copy("source://{$path}", "cache://{$temporaryPath}", ['visibility' => 'private']);

            $fullPath = $this->cacheDisk()->path($temporaryPath);
        }

        try {
            $attributes = $svg ? $this->svgAttributes($fullPath) : $this->imageAttributes($fullPath);
        } catch (\Exception $e) {
            $attributes = $svg ? $this->defaultSvgAttributes() : [];
        } finally {
            isset($temporaryPath) && $this->cacheDisk()->delete($temporaryPath);
        }

        return $attributes;
    }

    private function imageAttributes(string $path)
    {
        if (! file_exists($path)) {
            return ['width' => 0, 'height' => 0];
        }

        $size = @getimagesize($path);

        if ($size === false) {
            return ['width' => 0, 'height' => 0];
        }

        return $this->orient($size[0], $size[1], $path);
    }

    /**
     * Dimensions and orientation live at the start of an image, so on a remote
     * disk only that much is read rather than downloading the whole file.
     *
     * Returns null when they could not be found, and the file should be copied instead.
     */
    private function imageAttributesFromHeader(FilesystemAdapter $source, string $path): ?array
    {
        try {
            $stream = $this->headerStream($source, $path);
        } catch (\Throwable $e) {
            return null;
        }

        if (! is_resource($stream)) {
            return null;
        }

        $header = '';
        $window = self::HEADER_WINDOW;

        try {
            while (true) {
                $header .= (string) stream_get_contents($stream, $window - strlen($header));

                if ($header !== '' && ($size = @getimagesizefromstring($header))) {
                    return $this->orient($size[0], $size[1], $header, true);
                }

                if (feof($stream)) {
                    // The whole file has been read, so a copy has nothing more to offer.
                    return ['width' => 0, 'height' => 0];
                }

                if (strlen($header) < $window || $window >= self::HEADER_LIMIT) {
                    return null;
                }

                $window *= 2;
            }
        } catch (\Throwable $e) {
            return null;
        } finally {
            is_resource($stream) && fclose($stream);
        }
    }

    /**
     * @return resource|null
     */
    private function headerStream(FilesystemAdapter $source, string $path)
    {
        // Laravel buffers S3 reads in full unless told otherwise, so ask for ranges instead.
        if ($source instanceof AwsS3V3Adapter) {
            return StreamWrapper::getResource(
                new S3MetadataStream($source, $path, $source->size($path), self::S3_CHUNK_SIZE)
            );
        }

        return $source->readStream($path);
    }

    private function orient(int $width, int $height, string $image, bool $isContents = false): array
    {
        if (in_array($this->orientation($image, $isContents), [5, 6, 7, 8])) {
            [$width, $height] = [$height, $width];
        }

        return compact('width', 'height');
    }

    private function orientation(string $image, bool $isContents): int
    {
        if (! function_exists('exif_read_data')) {
            return 1;
        }

        if (! $isContents) {
            $exif = @exif_read_data($image);

            return $exif['Orientation'] ?? 1;
        }

        $stream = fopen('php://memory', 'r+');
        fwrite($stream, $image);
        rewind($stream);

        try {
            $exif = @exif_read_data($stream);
        } finally {
            fclose($stream);
        }

        return $exif['Orientation'] ?? 1;
    }

    private function svgAttributes(string $path)
    {
        $svg = simplexml_load_file($path);

        if ($svg['width'] && $svg['height']
            && is_numeric((string) $svg['width'])
            && is_numeric((string) $svg['height'])) {
            return ['width' => (float) $svg['width'], 'height' => (float) $svg['height']];
        } elseif ($svg['viewBox']) {
            [,,$width, $height] = preg_split('/[\s,]+/', $svg['viewBox'] ?: '');

            return ['width' => (float) $width, 'height' => (float) $height];
        }

        return $this->defaultSvgAttributes();
    }

    private function defaultSvgAttributes()
    {
        return ['width' => 300, 'height' => 150];
    }

    private function mountManager($source, $cache)
    {
        return new MountManager([
            'source' => $source,
            'cache' => $cache,
        ]);
    }

    private function cacheDisk()
    {
        return $this->cacheDisk ??= Storage::build([
            'driver' => 'local',
            'root' => storage_path('statamic/attributes-cache'),
        ]);
    }
}

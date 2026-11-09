<?php

namespace Statamic\Imaging;

use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Support\Facades\Storage;
use League\Flysystem\Local\LocalFilesystemAdapter;
use League\Flysystem\MountManager;
use Statamic\Support\Str;

class Attributes
{
    private $cacheDisk;

    public function from(FilesystemAdapter $source, string $path)
    {
        if ($source->getAdapter() instanceof LocalFilesystemAdapter) {
            $fullPath = $source->path($path);
        } else {
            $temporaryPath = Str::random(32).'.'.pathinfo($path, PATHINFO_EXTENSION);

            $manager = $this->mountManager($source->getDriver(), $this->cacheDisk()->getDriver());
            $manager->copy("source://{$path}", "cache://{$temporaryPath}", ['visibility' => 'private']);

            $fullPath = $this->cacheDisk()->path($temporaryPath);
        }

        $svg = Str::endsWith($path, '.svg');

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

        [$width, $height] = $size;

        if (function_exists('exif_read_data')) {
            $exif = @exif_read_data($path);
            $orientation = $exif['Orientation'] ?? 1;

            if (in_array($orientation, [5, 6, 7, 8])) {
                [$width, $height] = [$height, $width];
            }
        }

        return compact('width', 'height');
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

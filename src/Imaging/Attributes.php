<?php

namespace Statamic\Imaging;

use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Support\Facades\Storage;
use League\Flysystem\Local\LocalFilesystemAdapter;
use League\Flysystem\MountManager;
use Statamic\Support\Str;

class Attributes
{
    use DetectsTone;

    private $cacheDisk;

    public function from(FilesystemAdapter $source, string $path)
    {
        if ($source->getAdapter() instanceof LocalFilesystemAdapter) {
            $this->cacheDisk = $source;
        } else {
            $manager = $this->mountManager($source->getDriver(), $this->cacheDisk()->getDriver());

            if ($manager->has($destination = "cache://{$path}")) {
                $manager->delete($destination);
            }

            $manager->copy("source://{$path}", $destination, ['visibility' => 'private']);
        }

        $svg = Str::endsWith($path, '.svg');

        try {
            $attributes = $svg ? $this->svgAttributes($path) : $this->imageAttributes($path);
        } catch (\Exception $e) {
            $attributes = $svg ? $this->defaultSvgAttributes() : [];
        } finally {
            isset($manager) && $manager->delete($destination);
        }

        return $attributes;
    }

    private function imageAttributes(string $path)
    {
        $fullPath = $this->prefixPath($path);

        if (! file_exists($fullPath)) {
            return ['width' => 0, 'height' => 0, 'tone' => null];
        }

        $size = @getimagesize($fullPath);

        if ($size === false) {
            return ['width' => 0, 'height' => 0, 'tone' => null];
        }

        [$width, $height] = $size;

        $tone = $this->detectTone($fullPath);

        return compact('width', 'height', 'tone');
    }

    private function svgAttributes(string $path)
    {
        $svg = simplexml_load_file($this->prefixPath($path));

        if ($svg['width'] && $svg['height']
            && is_numeric((string) $svg['width'])
            && is_numeric((string) $svg['height'])) {
            $attrs = ['width' => (float) $svg['width'], 'height' => (float) $svg['height']];
        } elseif ($svg['viewBox']) {
            [,,$width, $height] = preg_split('/[\s,]+/', $svg['viewBox'] ?: '');
            $attrs = compact('width', 'height');
        } else {
            $attrs = $this->defaultSvgAttributes();
        }

        $attrs['tone'] = $this->detectSvgTone($this->prefixPath($path));

        return $attrs;
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
        return $this->cacheDisk ?: $this->cacheDisk = Storage::build([
            'driver' => 'local',
            'root' => storage_path('statamic/attributes-cache'),
        ]);
    }

    private function prefixPath($path)
    {
        return $this->cacheDisk()->path($path);
    }
}

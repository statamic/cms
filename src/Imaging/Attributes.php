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
            $this->cacheDisk = $source;
        } else {
            $manager = $this->mountManager($source->getDriver(), $this->cacheDisk()->getDriver());

            if ($manager->has($destination = "cache://{$path}")) {
                $manager->delete($destination);
            }

            $manager->copy("source://{$path}", $destination);
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
        [$width, $height] = getimagesize($this->prefixPath($path));

        return compact('width', 'height');
    }

    private function svgAttributes(string $path)
    {
        $svg = simplexml_load_file($this->prefixPath($path));

        if ($svg['width'] && $svg['height']
            && is_numeric((string) $svg['width'])
            && is_numeric((string) $svg['height'])) {
            return ['width' => (float) $svg['width'], 'height' => (float) $svg['height']];
        } elseif ($svg['viewBox']) {
            [,,$width, $height] = preg_split('/[\s,]+/', $svg['viewBox'] ?: '');

            return compact('width', 'height');
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

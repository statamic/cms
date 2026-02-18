<?php

namespace Statamic\Imaging;

use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\ImageManager;
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

    private function detectTone(string $fullPath): ?string
    {
        try {
            $driver = config('statamic.assets.image_manipulation.driver', 'gd');

            $manager = match ($driver) {
                'gd' => ImageManager::gd(),
                'imagick' => ImageManager::imagick(),
                default => ImageManager::withDriver($driver),
            };

            $image = $manager->read($fullPath);
            $image->scaleDown(64, 64);
            $w = $image->width();
            $h = $image->height();

            $sum = 0;
            $count = 0;
            $step = max(1, (int) ceil(($w * $h) / 256));
            $i = 0;

            for ($y = 0; $y < $h; $y++) {
                for ($x = 0; $x < $w; $x++) {
                    if ($i++ % $step !== 0) {
                        continue;
                    }
                    $color = $image->pickColor($x, $y);
                    $r = $color->red()->toInt() / 255;
                    $g = $color->green()->toInt() / 255;
                    $b = $color->blue()->toInt() / 255;
                    $l = 0.299 * $r + 0.587 * $g + 0.114 * $b;
                    $sum += $l;
                    $count++;
                }
            }

            $avg = $count > 0 ? $sum / $count : 0.5;

            return $avg >= 0.4 ? 'light' : 'dark';
        } catch (\Throwable $e) {
            return null;
        }
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

    private function detectSvgTone(string $fullPath): ?string
    {
        if (! extension_loaded('imagick')) {
            return null;
        }

        try {
            $im = new \Imagick();
            $im->setBackgroundColor(new \ImagickPixel('transparent'));
            $im->readImage($fullPath);
            $im->scaleImage(64, 64, true);
            $w = $im->getImageWidth();
            $h = $im->getImageHeight();

            $sum = 0;
            $count = 0;
            $step = max(1, (int) ceil(($w * $h) / 256));
            $i = 0;

            for ($y = 0; $y < $h; $y++) {
                for ($x = 0; $x < $w; $x++) {
                    if ($i++ % $step !== 0) {
                        continue;
                    }
                    $pixel = $im->getImagePixelColor($x, $y);
                    $c = $pixel->getColor(true);
                    if ($c['a'] < 0.1) {
                        continue;
                    }
                    $l = 0.299 * $c['r'] + 0.587 * $c['g'] + 0.114 * $c['b'];
                    $sum += $l;
                    $count++;
                }
            }

            $im->destroy();

            if ($count === 0) {
                return null;
            }

            $avg = $sum / $count;

            return $avg >= 0.4 ? 'light' : 'dark';
        } catch (\Throwable $e) {
            return null;
        }
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

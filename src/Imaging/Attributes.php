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
        if (extension_loaded('imagick')) {
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

                if ($count > 0) {
                    $avg = $sum / $count;

                    return $avg >= 0.4 ? 'light' : 'dark';
                }
            } catch (\Throwable $e) {
                // Fall through to XML-based fallback
            }
        }

        return $this->detectSvgToneFromSvgColors($fullPath);
    }

    /**
     * Fallback when Imagick is not available: parse SVG fill/stroke colors and derive dominant tone.
     */
    private function detectSvgToneFromSvgColors(string $fullPath): ?string
    {
        $svg = @simplexml_load_file($fullPath);

        if ($svg === false) {
            return null;
        }

        $svg->registerXPathNamespace('svg', 'http://www.w3.org/2000/svg');
        $elements = $svg->xpath('//*') ?: [];
        if ($elements === []) {
            $elements = $svg->xpath('//svg:*') ?: [];
        }

        static $drawingElements = ['path', 'circle', 'rect', 'ellipse', 'line', 'polyline', 'polygon', 'text', 'tspan', 'image'];

        $luminances = [];

        foreach ($elements as $el) {
            $attrs = $el->attributes();
            if ($attrs === null) {
                continue;
            }
            $style = isset($attrs['style']) ? (string) $attrs['style'] : '';
            $elementLuminances = [];
            foreach (['fill', 'stroke'] as $attr) {
                $value = isset($attrs[$attr]) ? trim((string) $attrs[$attr]) : null;
                if ($value === null && $style !== '') {
                    $value = $this->parseStyleColor($style, $attr);
                }
                if ($value === null || $value === '' || $value === 'none' || $value === 'transparent') {
                    continue;
                }
                $lum = $this->colorToLuminance($value);
                if ($lum !== null) {
                    $elementLuminances[] = $lum;
                }
            }
            if ($elementLuminances !== []) {
                $luminances = array_merge($luminances, $elementLuminances);
            } else {
                $localName = strtolower((string) $el->getName());
                $localName = Str::contains($localName, ':') ? Str::afterLast($localName, ':') : $localName;
                if (in_array($localName, $drawingElements, true)) {
                    // SVG default fill is black when not specified on drawing elements
                    $luminances[] = 0.0;
                }
            }
        }

        if ($luminances === []) {
            return null;
        }

        $avg = array_sum($luminances) / count($luminances);

        return $avg >= 0.4 ? 'light' : 'dark';
    }

    private function parseStyleColor(string $style, string $prop): ?string
    {
        if (! preg_match('/\b'.preg_quote($prop, '/').'\s*:\s*([^;]+)/i', $style, $m)) {
            return null;
        }
        $value = trim($m[1]);
        if ($value === 'none' || $value === 'transparent') {
            return null;
        }

        return $value;
    }

    /**
     * Parse a CSS/SVG color value to relative luminance (0–1). Returns null if unparseable.
     */
    private function colorToLuminance(string $value): ?float
    {
        $value = strtolower(trim($value));

        if ($value === 'currentcolor' || $value === 'currentColor') {
            return 0.5;
        }

        if (preg_match('/^#([0-9a-f]{3})$/i', $value, $m)) {
            $r = hexdec($m[1][0].$m[1][0]) / 255;
            $g = hexdec($m[1][1].$m[1][1]) / 255;
            $b = hexdec($m[1][2].$m[1][2]) / 255;

            return 0.299 * $r + 0.587 * $g + 0.114 * $b;
        }
        if (preg_match('/^#([0-9a-f]{6})$/i', $value, $m)) {
            $r = hexdec(substr($m[1], 0, 2)) / 255;
            $g = hexdec(substr($m[1], 2, 2)) / 255;
            $b = hexdec(substr($m[1], 4, 2)) / 255;

            return 0.299 * $r + 0.587 * $g + 0.114 * $b;
        }
        if (preg_match('/^rgb\s*\(\s*(\d+)\s*,\s*(\d+)\s*,\s*(\d+)\s*\)$/', $value, $m)) {
            $r = min(255, (int) $m[1]) / 255;
            $g = min(255, (int) $m[2]) / 255;
            $b = min(255, (int) $m[3]) / 255;

            return 0.299 * $r + 0.587 * $g + 0.114 * $b;
        }
        if (preg_match('/^rgba\s*\(\s*(\d+)\s*,\s*(\d+)\s*,\s*(\d+)\s*,\s*[\d.]+\s*\)$/', $value, $m)) {
            $r = min(255, (int) $m[1]) / 255;
            $g = min(255, (int) $m[2]) / 255;
            $b = min(255, (int) $m[3]) / 255;

            return 0.299 * $r + 0.587 * $g + 0.114 * $b;
        }

        $named = [
            'black' => 0, 'white' => 1, 'red' => 0.212, 'lime' => 0.715, 'blue' => 0.072,
            'gray' => 0.5, 'grey' => 0.5, 'silver' => 0.753, 'maroon' => 0.144, 'green' => 0.357,
            'navy' => 0.028, 'yellow' => 0.927, 'olive' => 0.502, 'purple' => 0.132, 'teal' => 0.357,
            'fuchsia' => 0.284, 'aqua' => 0.787, 'orange' => 0.695,
        ];
        if (isset($named[$value])) {
            return $named[$value];
        }

        return null;
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

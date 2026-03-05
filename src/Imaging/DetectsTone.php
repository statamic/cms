<?php

namespace Statamic\Imaging;

use Intervention\Image\ImageManager;
use Statamic\Support\Str;

trait DetectsTone
{
    /**
     * Minimum alpha (0-255) for a pixel to be included in tone detection.
     * Pixels below this are treated as transparent and skipped so that
     * transparent PNGs are judged by their visible content (e.g. black
     * logo on transparent → dark).
     */
    private const TONE_ALPHA_THRESHOLD = 26;

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
                    $alpha = $color->alpha()->toInt();
                    if ($alpha < self::TONE_ALPHA_THRESHOLD) {
                        continue;
                    }
                    $r = $color->red()->toInt() / 255;
                    $g = $color->green()->toInt() / 255;
                    $b = $color->blue()->toInt() / 255;
                    $l = 0.299 * $r + 0.587 * $g + 0.114 * $b;
                    $sum += $l;
                    $count++;
                }
            }

            if ($count === 0) {
                return null;
            }

            $avg = $sum / $count;

            return $avg >= 0.4 ? 'light' : 'dark';
        } catch (\Throwable $e) {
            return null;
        }
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
}

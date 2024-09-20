<?php

namespace Statamic\Imaging;

use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Support\Facades\Storage;
use League\Flysystem\Local\LocalFilesystemAdapter;
use League\Flysystem\MountManager;
use Statamic\Contracts\Assets\Asset;

use function storage_path;

class AsciiArt
{
    private Filesystem $cacheDisk;

    public function convert(string|Asset $item, ?int $scale = null): string
    {
        if ($item instanceof Asset) {
            $item = $this->getLocalAssetPath($item);
        }

        return $this->convertImageToAscii($item, $scale);
    }

    private function getLocalAssetPath(Asset $asset): string
    {
        if (! in_array($asset->extension(), ['jpg', 'jpeg', 'png'])) {
            throw new \Exception('Can only create ascii art from jpg, jpeg, or png images.');
        }

        $this->ensureLocalCopyExists($asset);

        return $this->cacheDisk()->path($asset->path());
    }

    private function ensureLocalCopyExists(Asset $asset): void
    {
        $source = $asset->container()->disk()->filesystem();
        $path = $asset->path();

        if ($source->getAdapter() instanceof LocalFilesystemAdapter) {
            $this->cacheDisk = $source;
        } else {
            $manager = $this->mountManager($source->getDriver(), $this->cacheDisk()->getDriver());

            if ($manager->has($destination = "cache://{$path}")) {
                $manager->delete($destination);
            }

            $manager->copy("source://{$path}", $destination, ['visibility' => 'private']);
        }
    }

    private function mountManager($source, $cache)
    {
        return new MountManager([
            'source' => $source,
            'cache' => $cache,
        ]);
    }

    private function cacheDisk(): Filesystem
    {
        return $this->cacheDisk ??= Storage::build([
            'driver' => 'local',
            'root' => storage_path('statamic/ascii-art-cache'),
        ]);
    }

    /**
     * Credit to https://github.com/walterrdev/ASCII-Art-php
     */
    private function convertImageToAscii(string $path, ?int $scale = null): string
    {
        $chars = "$@B%8&WM#*oahkbdpqwmZO0QLCJUYXzcvunxrjft/\\|()1{}[]?-_+~<>i!lI;:,\"^`'. ";
        $chars = str_split($chars);

        $extension = pathinfo($path, PATHINFO_EXTENSION);

        $im = match ($extension) {
            'png' => imagecreatefrompng($path),
            'jpeg', 'jpg' => imagecreatefromjpeg($path),
        };

        if ($im == null) {
            throw new \Exception('Could not create image from file.');
        }

        if ($scale) {
            $im = imagescale($im, $scale);
        }

        $size = getimagesize($path);

        if ($scale) {
            $width = $scale;
            $height = (int) (($scale * $size[1]) / $size[0]);
        } else {
            [$width, $height] = $size;
        }

        $str = '';
        for ($i = 0; $i < $height; $i++) {
            for ($j = 0; $j < $width; $j++) {
                $rgb = imagecolorat($im, $j, $i);
                $r = ($rgb >> 16) & 0xFF;
                $g = ($rgb >> 8) & 0xFF;
                $b = $rgb & 0xFF;

                $bw = (0.21 * $r) + (0.72 * $g) + (0.07 * $b);

                $char = $chars[(int) ($bw / 3.7)]; //255/70 = 3.64
                $str .= $char;
            }
            $str .= PHP_EOL;
        }

        return $str;
    }
}

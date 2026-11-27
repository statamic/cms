<?php

namespace Tests\Fakes;

/**
 * Builds image files that are cheap to generate but shaped like real uploads:
 * a valid header, optional metadata in front of the dimensions, and a large body.
 */
class ImageFixtures
{
    /**
     * @param  int  $size  Total size of the file in bytes.
     * @param  int|null  $orientation  EXIF orientation to embed.
     * @param  int  $headerPadding  Bytes of metadata to place before the dimensions, like an embedded thumbnail or color profile would.
     */
    public static function jpeg(int $width, int $height, int $size, ?int $orientation = null, int $headerPadding = 0): string
    {
        $jpeg = self::render('imagejpeg', $width, $height);

        $segments = $orientation ? self::exifSegment($orientation) : '';

        while ($headerPadding > 0) {
            $length = min($headerPadding, 65000);
            $segments .= "\xFF\xEC".pack('n', $length + 2).str_repeat("\0", $length);
            $headerPadding -= $length;
        }

        // Segments go straight after the two byte start-of-image marker.
        $jpeg = substr($jpeg, 0, 2).$segments.substr($jpeg, 2);

        return self::pad($jpeg, $size);
    }

    public static function png(int $width, int $height, int $size): string
    {
        return self::pad(self::render('imagepng', $width, $height), $size);
    }

    public static function gif(int $width, int $height, int $size): string
    {
        return self::pad(self::render('imagegif', $width, $height), $size);
    }

    public static function webp(int $width, int $height, int $size): string
    {
        return self::pad(self::render('imagewebp', $width, $height), $size);
    }

    public static function junk(int $size): string
    {
        return self::pad('', $size);
    }

    private static function render(string $function, int $width, int $height): string
    {
        $image = imagecreatetruecolor($width, $height);

        ob_start();
        $function($image);

        return ob_get_clean();
    }

    private static function exifSegment(int $orientation): string
    {
        $tiff = "MM\x00\x2A\x00\x00\x00\x08" // Big endian TIFF header, first IFD at offset 8.
            ."\x00\x01" // One entry.
            ."\x01\x12\x00\x03\x00\x00\x00\x01".pack('n', $orientation)."\x00\x00" // Orientation.
            ."\x00\x00\x00\x00"; // No further IFDs.

        $payload = "Exif\x00\x00".$tiff;

        return "\xFF\xE1".pack('n', strlen($payload) + 2).$payload;
    }

    private static function pad(string $contents, int $size): string
    {
        $padding = max(0, $size - strlen($contents));

        // Repeating a random block is far quicker than generating megabytes of randomness.
        return $contents.substr(str_repeat(random_bytes(1024), (int) ceil($padding / 1024)), 0, $padding);
    }
}

<?php

namespace Statamic\Assets;

use Illuminate\Support\Facades\Log;

use function Statamic\trans as __;

class CropAspectRatios
{
    /**
     * @return array<int, array{label: string, value: float}>
     */
    public static function all(): array
    {
        $ratios = config('statamic.assets.crop_aspect_ratios', []);

        if (! is_array($ratios)) {
            return [];
        }

        return collect($ratios)
            ->map(fn ($entry, $key) => self::parse($entry, $key))
            ->filter()
            ->values()
            ->all();
    }

    /**
     * @return array{label: string, value: float}|null
     */
    private static function parse(mixed $entry, mixed $key): ?array
    {
        if (is_array($entry)) {
            return self::parseArrayEntry($entry, $key);
        }

        if (! is_string($entry) && ! is_numeric($entry)) {
            self::warn($entry, $key);

            return null;
        }

        $value = self::ratioToFloat($entry);

        if ($value === null) {
            self::warn($entry, $key);

            return null;
        }

        return [
            'label' => __((string) $entry),
            'value' => $value,
        ];
    }

    /**
     * @param  array<mixed>  $entry
     * @return array{label: string, value: float}|null
     */
    private static function parseArrayEntry(array $entry, mixed $key): ?array
    {
        $label = $entry['label'] ?? null;
        $ratio = $entry['ratio'] ?? null;

        $labelIsStringable = is_string($label) || is_numeric($label) || $label instanceof \Stringable;

        if (! $labelIsStringable || (! is_string($ratio) && ! is_numeric($ratio))) {
            self::warn($entry, $key);

            return null;
        }

        $value = self::ratioToFloat($ratio);

        if ($value === null) {
            self::warn($entry, $key);

            return null;
        }

        return [
            'label' => __((string) $label),
            'value' => $value,
        ];
    }

    private static function ratioToFloat(string|int|float $ratio): ?float
    {
        if (is_numeric($ratio)) {
            $value = (float) $ratio;

            return $value > 0 ? $value : null;
        }

        if (! str_contains($ratio, ':')) {
            return null;
        }

        [$width, $height] = explode(':', $ratio, 2);

        if (! is_numeric($width) || ! is_numeric($height)) {
            return null;
        }

        $width = (float) $width;
        $height = (float) $height;

        if ($width <= 0 || $height <= 0) {
            return null;
        }

        return $width / $height;
    }

    private static function warn(mixed $entry, mixed $key): void
    {
        Log::warning('Invalid crop aspect ratio config entry.', [
            'config_key' => $key,
            'entry' => $entry,
        ]);
    }
}

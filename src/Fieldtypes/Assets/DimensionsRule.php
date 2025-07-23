<?php

namespace Statamic\Fieldtypes\Assets;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Statamic\Facades\Asset;
use Statamic\Statamic;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class DimensionsRule implements ValidationRule
{
    public function __construct(protected $parameters)
    {
        $this->parameters = array_reduce($parameters, function ($result, $item) {
            [$key, $value] = array_pad(explode('=', $item, 2), 2, null);

            $result[$key] = $value;

            return $result;
        });
    }

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $size = [0, 0];

        if ($value instanceof UploadedFile) {
            if (in_array($value->getMimeType(), ['image/svg+xml', 'image/svg'])) {
                return;
            }

            $size = getimagesize($value->getPathname());
        } elseif ($asset = Asset::find($value)) {
            if ($asset->isSvg()) {
                return;
            }

            $size = $asset->dimensions();
        }

        [$width, $height] = $size;
        if (! is_int($width) || ! is_int($height)) {
            $fail(__('statamic::validation.dimensions.unknown'));

            return;
        }

        if ($message = $this->message($width, $height)) {
            $fail($message);
        }
    }

    public function message(int $width, int $height): ?string
    {
        $invalid_ratio = $this->validateRatio($width, $height);
        $invalid_width = $this->validateWidth($width);
        $invalid_height = $this->validateHeight($height);
        $key = match (true) {
            $invalid_ratio => 'ratio',
            $invalid_width && $invalid_height && $invalid_width === $invalid_height => 'same',
            $invalid_width && $invalid_height && $invalid_width !== $invalid_height => 'different',
            (bool) $invalid_width => 'width',
            (bool) $invalid_height => 'height',
            default => null,
        };

        if (! $key) {
            return null;
        }

        $prefix = Statamic::isCpRoute() ? 'statamic::' : '';

        $comparisons = [
            'min' => __("{$prefix}validation.dimensions.min"),
            'max' => __("{$prefix}validation.dimensions.max"),
            'exact' => __("{$prefix}validation.dimensions.exact"),
        ];

        return __("{$prefix}validation.dimensions.{$key}", [
            'width' => $this->parameters['width'] ?? $this->parameters['min_width'] ?? $this->parameters['max_width'] ?? null,
            'height' => $this->parameters['height'] ?? $this->parameters['min_height'] ?? $this->parameters['max_height'] ?? null,
            'ratio' => $this->parameters['ratio'] ?? null,
            'comparison' => $comparisons[$invalid_width] ?? '',
            'comparison_width' => $comparisons[$invalid_width] ?? '',
            'comparison_height' => $comparisons[$invalid_height] ?? '',
        ]);
    }

    public function validateWidth(int $width): ?string
    {
        return match (true) {
            isset($this->parameters['width']) && $this->parameters['width'] != $width => 'exact',
            isset($this->parameters['min_width']) && $this->parameters['min_width'] > $width => 'min',
            isset($this->parameters['max_width']) && $this->parameters['max_width'] < $width => 'max',
            default => null,
        };
    }

    public function validateHeight(int $height): ?string
    {
        return match (true) {
            isset($this->parameters['height']) && $this->parameters['height'] != $height => 'exact',
            isset($this->parameters['min_height']) && $this->parameters['min_height'] > $height => 'min',
            isset($this->parameters['max_height']) && $this->parameters['max_height'] < $height => 'max',
            default => null,
        };
    }

    public function validateRatio(int $width, int $height): bool
    {
        if (! isset($this->parameters['ratio'])) {
            return false;
        }

        [$numerator, $denominator] = array_replace(
            [1, 1],
            array_filter(sscanf($this->parameters['ratio'], '%f/%d'))
        );

        $precision = 1 / (max($width, $height) + 1);

        return abs($numerator / $denominator - $width / $height) > $precision;
    }
}

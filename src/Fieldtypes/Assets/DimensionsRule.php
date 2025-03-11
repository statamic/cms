<?php

namespace Statamic\Fieldtypes\Assets;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Statamic\Facades\Asset;
use Statamic\Statamic;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class DimensionsRule implements ValidationRule
{
    public function __construct(protected $parameters) {}

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $size = [0, 0];

        if ($value instanceof UploadedFile) {
            if (in_array($value->getMimeType(), ['image/svg+xml', 'image/svg'])) {
                return;
            }

            $size = getimagesize($value->getPathname());
        } else if ($asset = Asset::find($value)) {
            if ($asset->isSvg()) {
                return;
            }

            $size = $asset->dimensions();
        }

        [$width, $height] = $size;

        $parameters = $this->parseNamedParameters($this->parameters);

        $invalid_ratio = $this->failsRatioCheck($parameters, $width, $height);
        $invalid_width = match (true) {
            isset($parameters['width']) && $parameters['width'] != $width => 'exact',
            isset($parameters['min_width']) && $parameters['min_width'] > $width => 'min',
            isset($parameters['max_width']) && $parameters['max_width'] < $width => 'max',
            default => false,
        };
        $invalid_height = match (true) {
            isset($parameters['height']) && $parameters['height'] != $height => 'exact',
            isset($parameters['min_height']) && $parameters['min_height'] > $height => 'min',
            isset($parameters['max_height']) && $parameters['max_height'] < $height => 'max',
            default => false,
        };

        $key = match (true) {
            $invalid_ratio => 'ratio',
            $invalid_width && $invalid_height && $invalid_width === $invalid_height => 'same',
            $invalid_width && $invalid_height && $invalid_width !== $invalid_height => 'different',
            !!$invalid_width => 'width',
            !!$invalid_height => 'height',
            default => null,
        };

        if ($key) {
            $prefix = Statamic::isCpRoute() ? 'statamic::' : '';

            $comparisons = [
                'min' => __("{$prefix}validation.dimensions.min"),
                'max' => __("{$prefix}validation.dimensions.max"),
                'exact' => __("{$prefix}validation.dimensions.exact"),
            ];

            $fail(__("{$prefix}validation.dimensions.{$key}", [
                'width' => $parameters['width'] ?? $parameters['min_width'] ?? $parameters['max_width'] ?? null,
                'height' => $parameters['height'] ?? $parameters['min_height'] ?? $parameters['max_height'] ?? null,
                'ratio' => $parameters['ratio'] ?? null,
                'comparison' => $comparisons[$invalid_width] ?? '',
                'comparison_width' => $comparisons[$invalid_width] ?? '',
                'comparison_height' => $comparisons[$invalid_height] ?? '',
            ]));
        }
    }

    /**
     * Parse named parameters to $key => $value items.
     *
     * @param  array  $parameters
     * @return array
     */
    protected function parseNamedParameters($parameters)
    {
        return array_reduce($parameters, function ($result, $item) {
            [$key, $value] = array_pad(explode('=', $item, 2), 2, null);

            $result[$key] = $value;

            return $result;
        });
    }

    /**
     * Determine if the given parameters fail a dimension ratio check.
     *
     * @param  array  $parameters
     * @param  int  $width
     * @param  int  $height
     * @return bool
     */
    protected function failsRatioCheck($parameters, $width, $height)
    {
        if (! isset($parameters['ratio'])) {
            return false;
        }

        [$numerator, $denominator] = array_replace(
            [1, 1],
            array_filter(sscanf($parameters['ratio'], '%f/%d'))
        );

        $precision = 1 / (max($width, $height) + 1);

        return abs($numerator / $denominator - $width / $height) > $precision;
    }
}

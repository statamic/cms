<?php

namespace Statamic\Fieldtypes\Assets;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Statamic\Contracts\GraphQL\CastableToValidationString;
use Statamic\Facades\Asset;
use Statamic\Statamic;
use Stringable;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class DimensionsRule implements CastableToValidationString, Stringable, ValidationRule
{
    protected array $raw_parameters;

    public function __construct(protected $parameters)
    {
        $this->raw_parameters = $parameters;
        $this->parameters = array_reduce($parameters, function ($acc, $item) {
            [$key, $value] = array_pad(explode('=', $item, 2), 2, null);
            $acc[$key] = $value;

            return $acc;
        }, []);
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

        if ($this->failsBasicDimensionChecks($this->parameters, $width, $height) ||
            $this->failsRatioCheck($this->parameters, $width, $height)) {
            $fail($this->message());
        }
    }

    public function message(): string
    {
        return __((Statamic::isCpRoute() ? 'statamic::' : '').'validation.dimensions');
    }

    /**
     * Test if the given width and height fail any conditions.
     *
     * @param  array  $parameters
     * @param  int  $width
     * @param  int  $height
     * @return bool
     */
    protected function failsBasicDimensionChecks($parameters, $width, $height)
    {
        return (isset($parameters['width']) && $parameters['width'] != $width) ||
               (isset($parameters['min_width']) && $parameters['min_width'] > $width) ||
               (isset($parameters['max_width']) && $parameters['max_width'] < $width) ||
               (isset($parameters['height']) && $parameters['height'] != $height) ||
               (isset($parameters['min_height']) && $parameters['min_height'] > $height) ||
               (isset($parameters['max_height']) && $parameters['max_height'] < $height);
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
            [1, 1], array_filter(sscanf($parameters['ratio'], '%f/%d'))
        );

        $precision = 1 / (max($width, $height) + 1);

        return abs($numerator / $denominator - $width / $height) > $precision;
    }

    public function __toString()
    {
        return 'dimensions:'.implode(',', $this->raw_parameters);
    }

    public function toGqlValidationString(): string
    {
        return $this->__toString();
    }
}

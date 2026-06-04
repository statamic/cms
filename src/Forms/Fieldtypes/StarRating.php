<?php

namespace Statamic\Forms\Fieldtypes;

use Statamic\Forms\Fields\FormFieldtype;
use Statamic\Support\Arr;

use function Statamic\trans as __;

class StarRating extends FormFieldtype
{
    protected static $fieldtype = 'star_rating';
    protected $description = 'A star rating input for collecting ratings.';
    protected $icon = 'star';
    protected $categories = ['rate'];
    protected $order = 3;

    public function configFieldItems(): array
    {
        return [
            'max_stars' => [
                'display' => __('Max Stars'),
                'instructions' => __('The maximum number of stars respondents can choose.'),
                'type' => 'integer',
                'default' => 5,
                'width' => 50,
                'validate' => 'integer|min:1|max:10',
            ],
        ];
    }

    public function toFieldArray(): array
    {
        $maxStars = max(1, min(10, (int) ($this->config('max_stars') ?: 5)));

        return [
            'type' => 'star_rating',
            'max_stars' => $maxStars,
            'stars' => range(1, $maxStars),
            ...Arr::except($this->config(), ['type', 'max_stars']),
        ];
    }

    public function example(): ?array
    {
        return [
            'config' => [
                'display' => __('How would you rate your experience?'),
                'max_stars' => 5,
            ],
            'value' => 4,
        ];
    }
}

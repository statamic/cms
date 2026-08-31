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
                'instructions' => __('The maximum number of selectable stars.'),
                'type' => 'integer',
                'default' => 5,
                'min' => 1,
                'max' => 10,
                'width' => 50,
                'validate' => 'integer|min:1|max:10',
            ],
            'allow_half_stars' => [
                'display' => __('Allow Half Stars'),
                'instructions' => __('Allow ratings like 4.5.'),
                'type' => 'toggle',
                'default' => false,
                'width' => 50,
            ],
        ];
    }

    public function toFieldArray(): array
    {
        $allowHalfStars = (bool) $this->config('allow_half_stars');
        $maxStars = max(1, min(10, (int) $this->config('max_stars', 5)));

        return [
            'type' => 'star_rating',
            'max_stars' => $maxStars,
            'allow_half_stars' => $allowHalfStars,
            'min' => $allowHalfStars ? 0.5 : 1,
            'step' => $allowHalfStars ? 0.5 : 1,
            ...Arr::except($this->config(), ['type', 'max_stars', 'allow_half_stars']),
        ];
    }

    public function example(): ?array
    {
        return [
            'config' => [
                'display' => 'How would you rate your experience?',
                'max_stars' => 5,
                'allow_half_stars' => true,
            ],
        ];
    }
}

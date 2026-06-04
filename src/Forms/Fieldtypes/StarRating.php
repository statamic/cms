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
                'width' => 50,
                'validate' => 'integer|min:1|max:10',
            ],
            'allow_half_stars' => [
                'display' => __('Allow Half Stars'),
                'instructions' => __('Support half-star ratings (e.g., 4.5 stars).'),
                'type' => 'toggle',
                'default' => false,
                'width' => 50,
            ],
        ];
    }

    public function toFieldArray(): array
    {
        $maxStars = max(1, min(10, (int) ($this->config('max_stars') ?: 5)));
        $allowHalfStars = (bool) $this->config('allow_half_stars');

        return [
            'type' => 'star_rating',
            'max_stars' => $maxStars,
            'allow_half_stars' => $allowHalfStars,
            'step' => $allowHalfStars ? 0.5 : 1,
            'stars' => range(1, $maxStars),
            'rating_values' => $this->ratingValues($maxStars, $allowHalfStars),
            'star_inputs' => collect(range(1, $maxStars))
                ->map(fn (int $star) => [
                    'star' => $star,
                    'half_value' => $allowHalfStars ? $star - 0.5 : null,
                    'full_value' => $star,
                ])
                ->all(),
            ...Arr::except($this->config(), ['type', 'max_stars', 'allow_half_stars']),
        ];
    }

    /**
     * @return array<int, float|int>
     */
    protected function ratingValues(int $maxStars, bool $allowHalfStars): array
    {
        if (! $allowHalfStars) {
            return range(1, $maxStars);
        }

        $values = [];

        for ($value = 0.5; $value <= $maxStars; $value += 0.5) {
            $values[] = $value;
        }

        return $values;
    }

    public function example(): ?array
    {
        return [
            'config' => [
                'display' => __('How would you rate your experience?'),
                'max_stars' => 5,
                'allow_half_stars' => true,
            ],
            'value' => 4.5,
        ];
    }
}

<?php

namespace Statamic\Forms\Fieldtypes;

use Illuminate\Support\Collection;
use Statamic\Forms\Charts\ChartOption;
use Statamic\Forms\Charts\HorizontalBar;
use Statamic\Forms\Fields\FormFieldtype;
use Statamic\Forms\Insights\StarRating as StarRatingInsight;
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
                'instructions' => __('statamic::form-fieldtypes.star_rating.config.max_stars.instructions'),
                'type' => 'integer',
                'default' => 5,
                'min' => 1,
                'max' => 10,
                'width' => 50,
                'validate' => 'integer|min:1|max:10',
            ],
            'allow_half_stars' => [
                'display' => __('Allow Half Stars'),
                'instructions' => __('statamic::form-fieldtypes.star_rating.config.allow_half_stars.instructions'),
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

    public function defaultChart(): ?string
    {
        return HorizontalBar::class;
    }

    public function chartOptions(Collection $values): ?Collection
    {
        $options = collect();
        $step = $this->config('allow_half_stars') ? 0.5 : 1;

        for ($stars = $this->maxStars(); $stars >= $step; $stars -= $step) {
            $options->push(new ChartOption($this->starKey($stars), icon: 'star-filled'));
        }

        return $options;
    }

    private function starKey(float $stars): string
    {
        return $stars == (int) $stars ? (string) (int) $stars : (string) $stars;
    }

    private function maxStars(): int
    {
        return max(1, min(10, (int) $this->config('max_stars', 5)));
    }

    public function insights(): array
    {
        return [new StarRatingInsight(total: $this->maxStars())];
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

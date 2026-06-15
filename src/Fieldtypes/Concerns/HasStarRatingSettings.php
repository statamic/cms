<?php

namespace Statamic\Fieldtypes\Concerns;

trait HasStarRatingSettings
{
    protected function starRatingSettings(): array
    {
        $allowHalfStars = (bool) $this->config('allow_half_stars');
        $maxStars = max(1, min(10, (int) $this->config('max_stars', 5)));

        return [
            'max_stars' => $maxStars,
            'allow_half_stars' => $allowHalfStars,
            // Must match step (half) or 1 (full) so thumb padding math aligns — see CSS-Tricks.
            'min' => $allowHalfStars ? 0.5 : 1,
            'step' => $allowHalfStars ? 0.5 : 1,
        ];
    }
}

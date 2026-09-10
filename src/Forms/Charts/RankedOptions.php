<?php

namespace Statamic\Forms\Charts;

use Illuminate\Support\Collection;

class RankedOptions extends Chart
{
    protected static $title = 'Ranking';

    protected ?string $component = 'ui-horizontal-lollipop-chart';
    protected ?string $icon = 'rank';

    public function props(Collection $values, ?Collection $options = null): array
    {
        $options ??= collect();

        $rankings = $values
            ->filter(fn ($value): bool => is_array($value))
            ->map(fn (array $value): array => array_map('strval', array_values($value)))
            ->values();

        $maxPoints = $rankings->count() * $options->count();

        $items = $options
            ->map(function (ChartOption $option) use ($rankings, $options): array {
                $positions = $rankings
                    ->map(fn ($ranking) => array_search($option->key, $ranking, true))
                    ->filter(fn ($position): bool => $position !== false)
                    ->map(fn ($position): int => $position + 1);

                return [
                    'key' => $option->key,
                    'label' => $option->label,
                    'count' => $positions->filter(fn ($position) => $position === 1)->count(),
                    'points' => $positions->map(fn ($position) => $options->count() - $position + 1)->sum(),
                    'average' => $positions->avg(),
                ];
            })
            ->sortBy(fn ($item): float => $item['average'] ?? PHP_FLOAT_MAX)
            ->values()
            ->map(fn ($item, $index): array => [
                'key' => $item['key'],
                'label' => $item['label'],
                'rank' => $index + 1,
                'count' => $item['count'],
                'percent' => $this->percent($item['points'], $maxPoints),
            ]);

        return ['items' => $items->all()];
    }
}

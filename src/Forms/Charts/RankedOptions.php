<?php

namespace Statamic\Forms\Charts;

use Illuminate\Support\Collection;
use Statamic\Forms\Fields\FormValueType;
use Statamic\Forms\Summary\FieldResponses;

class RankedOptions extends Chart
{
    protected static $title = 'Ranking';

    protected ?string $component = 'ui-horizontal-lollipop-chart';
    protected ?string $icon = 'rank';

    public function supports(): array
    {
        return [FormValueType::Ranking];
    }

    public function props(FieldResponses $responses, ?Collection $options = null): array
    {
        $options ??= collect();
        $optionCount = $options->count();
        $maxPoints = $responses->total() * $optionCount;

        $items = $options
            ->map(function (ChartOption $option) use ($responses, $optionCount): array {
                $byPosition = $responses->positions()->get($option->key, []);
                $appearances = array_sum($byPosition);
                $positionSum = 0;

                foreach ($byPosition as $position => $count) {
                    $positionSum += $count * $position;
                }

                return [
                    'key' => $option->key,
                    'label' => $option->label,
                    'count' => $byPosition[1] ?? 0,
                    'points' => $appearances * ($optionCount + 1) - $positionSum,
                    'average' => $appearances ? $positionSum / $appearances : null,
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

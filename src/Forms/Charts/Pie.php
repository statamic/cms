<?php

namespace Statamic\Forms\Charts;

use Illuminate\Support\Collection;

use function Statamic\trans as __;

class Pie extends Chart
{
    protected static $title = 'Pie chart';

    protected ?string $component = 'ui-pie-chart';
    protected ?string $icon = 'money-graph-pie-chart';
    protected ?int $limit = 4;

    public function props(Collection $values, ?Collection $options = null): array
    {
        [$items, $other] = $this->truncatedItems($values, $options);

        $props = ['items' => $items->all()];

        if ($other->isEmpty()) {
            return $props;
        }

        // The pie keeps its shape while the "other" slice is highlighted and its items are listed.
        $props['drilldown'] = [
            'items' => $this->cappedItems($other, $values->count())->all(),
            'segments' => $items->all(),
            'focusedIndex' => $items->search(fn (array $item): bool => $item['other'] ?? false),
        ];

        return $props;
    }

    private function cappedItems(Collection $other, int $total): Collection
    {
        if ($other->count() <= $this->limit) {
            return $other;
        }

        $rest = $other->slice($this->limit - 1);

        return $other
            ->take($this->limit - 1)
            ->push([
                'key' => 'more',
                'label' => __('+:count more', ['count' => $rest->count()]),
                'count' => $count = $rest->sum('count'),
                'percent' => $this->percent($count, $total),
            ])
            ->values();
    }
}

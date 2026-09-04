<?php

namespace Statamic\Forms\Charts;

use Illuminate\Support\Collection;
use Statamic\Extend\HasHandle;
use Statamic\Extend\HasTitle;
use Statamic\Extend\RegistersItself;

use function Statamic\trans as __;

abstract class Chart
{
    use HasHandle, HasTitle, RegistersItself;

    protected ?string $component = null;
    protected ?string $icon = null;
    protected ?int $limit = null;

    public function component(): string
    {
        return $this->component;
    }

    public function icon(): ?string
    {
        return $this->icon;
    }

    public function props(Collection $values, ?Collection $options = null): array
    {
        [$items, $other] = $this->truncatedItems($values, $options);

        $props = ['items' => $items->all()];

        if ($other->isEmpty()) {
            return $props;
        }

        $props['drilldown'] = $this->drilldown($items, $other, $values->count());

        return $props;
    }

    protected function truncatedItems(Collection $values, ?Collection $options): array
    {
        $total = $values->count();
        $items = $this->items($values, $options, $total);

        if (! $this->limit || $items->count() <= $this->limit) {
            return [$items->values(), collect()];
        }

        $keep = $items->sortByDesc('count')->take($this->limit - 1)->pluck('key');

        [$items, $other] = $items->partition(fn ($item): bool => $keep->contains($item['key']));

        $items->push([
            'key' => 'other',
            'label' => __('Other'),
            'count' => $count = $other->sum('count'),
            'percent' => $this->percent($count, $total),
            'other' => true,
        ]);

        return [$items->values(), $other->values()];
    }

    private function items(Collection $values, ?Collection $options, int $total): Collection
    {
        $counts = $values->flatten()->countBy(fn ($value) => $this->key($value));

        if ($options === null && $this->shouldBin($counts)) {
            return $this->binnedItems($counts, $total);
        }

        $options ??= $this->optionsFromValues($counts);

        return $options->map(fn (ChartOption $option) => array_filter([
            'key' => $option->key,
            'label' => $option->label,
            'count' => $count = $counts->get($option->key, 0),
            'percent' => $this->percent($count, $total),
            'icon' => $option->icon,
            'image' => $option->image,
            'badge' => $option->badge,
        ], fn ($value) => $value !== null));
    }

    private function shouldBin(Collection $counts): bool
    {
        return $counts->count() > 10 && $counts->keys()->every(fn ($key) => is_numeric($key));
    }

    private function binnedItems(Collection $counts, int $total): Collection
    {
        $keys = $counts->keys()->map(fn ($key) => (float) $key);
        $min = (int) floor($keys->min());
        $max = (int) ceil($keys->max());
        $step = max(1, (int) ceil(($max - $min + 1) / 8));

        return collect(range($min, $max, $step))->map(function ($start) use ($counts, $total, $step, $max) {
            $end = min($start + $step - 1, $max);
            $count = $counts
                ->filter(fn ($count, $key) => (float) $key >= $start && (float) $key < $start + $step)
                ->sum();

            return [
                'key' => "{$start}-{$end}",
                'label' => $start === $end ? (string) $start : "{$start}–{$end}",
                'count' => $count,
                'percent' => $this->percent($count, $total),
            ];
        })->values();
    }

    private function optionsFromValues(Collection $counts): Collection
    {
        $keys = $counts->keys();

        $keys = $keys->every(fn ($key) => is_numeric($key))
            ? $keys->sortBy(fn ($key) => (float) $key)
            : $keys->sortByDesc(fn ($key) => $counts->get($key));

        return $keys->map(fn ($value) => new ChartOption((string) $value));
    }

    private function key($value): string
    {
        if (is_bool($value)) {
            return $value ? 'true' : 'false';
        }

        return (string) $value;
    }

    protected function drilldown(Collection $items, Collection $other, int $total): array
    {
        return [
            'items' => $this->cappedItems($other, $total)->all(),
            'focusedIndex' => $items->search(fn (array $item): bool => $item['other'] ?? false),
        ];
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

    protected function percent(int $count, int $total): int
    {
        return $total ? (int) round($count / $total * 100) : 0;
    }
}

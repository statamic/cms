<?php

namespace Statamic\Forms\Charts;

use Illuminate\Support\Collection;
use Statamic\Extend\HasHandle;
use Statamic\Extend\HasTitle;
use Statamic\Extend\RegistersItself;
use Statamic\Forms\Fields\FormField;
use Statamic\Forms\Fields\FormValueType;
use Statamic\Forms\Summary\FieldResponses;
use Statamic\Support\Str;

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

    /** @return list<FormValueType> */
    abstract public function supports(): array;

    public function appliesTo(FormField $field): bool
    {
        return in_array($field->fieldtype()->valueType(), $this->supports(), true);
    }

    public function props(FieldResponses $responses, ?Collection $options = null): array
    {
        [$items, $other] = $this->truncatedItems($responses, $options);

        $props = ['items' => $items->all()];

        if ($other->isEmpty()) {
            return $props;
        }

        $props['drilldown'] = $this->drilldown($items, $other, $responses->total());

        return $props;
    }

    protected function truncatedItems(FieldResponses $responses, ?Collection $options): array
    {
        $total = $responses->total();
        $items = $this->items($responses->counts(), $options, $total);

        if (! $this->limit || $items->count() <= $this->limit) {
            return [$items->values(), collect()];
        }

        $keep = $items->sortByDesc('count')->take($this->limit - 1)->pluck('key');

        [$items, $other] = $items->partition(fn ($item): bool => $keep->contains($item['key']));

        $icons = $other->pluck('icon')->filter()->unique();

        $items->push(array_filter([
            'key' => 'other',
            'label' => __('Other'),
            'count' => $count = $other->sum('count'),
            'percent' => $this->percent($count, $total),
            'icon' => $icons->count() === 1 ? $icons->first() : null,
            'other' => true,
        ], fn ($value) => $value !== null));

        return [$items->values(), $other->values()];
    }

    private function items(Collection $counts, ?Collection $options, int $total): Collection
    {
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
        $decimals = $this->binDecimals($keys);
        $scale = 10 ** $decimals;
        $scaled = $counts->keys()->mapWithKeys(fn ($key) => [$key => round((float) $key * $scale, 6)]);
        $min = (int) floor($scaled->min());
        $max = (int) ceil($scaled->max());
        $step = max(1, (int) ceil(($max - $min + 1) / 8));
        $format = fn (int $value) => number_format($value / $scale, $decimals, '.', '');

        return collect(range($min, $max, $step))->map(function ($start) use ($counts, $total, $step, $max, $scaled, $format) {
            $end = min($start + $step - 1, $max);
            $count = $counts
                ->filter(fn ($count, $key) => $scaled[$key] >= $start && $scaled[$key] < $start + $step)
                ->sum();

            return [
                'key' => "{$format($start)}-{$format($end)}",
                'label' => $start === $end ? $format($start) : "{$format($start)}–{$format($end)}",
                'count' => $count,
                'percent' => $this->percent($count, $total),
            ];
        })->values();
    }

    private function binDecimals(Collection $values): int
    {
        $decimals = $values->map(function (float $value) {
            $fraction = rtrim(Str::after(number_format($value, 10, '.', ''), '.'), '0');

            return strlen($fraction);
        })->max();

        // No more decimals than it takes to split the range into about 8 ranges.
        $needed = max(0, (int) ceil(-log10(($values->max() - $values->min()) / 8)));

        return min($decimals, $needed);
    }

    private function optionsFromValues(Collection $counts): Collection
    {
        $keys = $counts->keys();

        $keys = $keys->every(fn ($key) => is_numeric($key))
            ? $keys->sortBy(fn ($key) => (float) $key)
            : $keys->sortByDesc(fn ($key) => $counts->get($key));

        return $keys->map(fn ($value) => new ChartOption((string) $value));
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

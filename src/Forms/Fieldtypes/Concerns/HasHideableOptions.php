<?php

namespace Statamic\Forms\Fieldtypes\Concerns;

use Illuminate\Support\Collection;
use Statamic\Forms\Charts\ChartOption;
use Statamic\Forms\Summary\FieldResponses;

trait HasHideableOptions
{
    private function options(): array
    {
        $options = $this->config('options') ?? [];

        if (! collect($options)->contains(fn ($option) => is_array($option))) {
            return $options;
        }

        return collect($options)
            ->map(fn ($option, $key) => [
                'key' => is_array($option) ? ($option['key'] ?? $key) : $key,
                'value' => is_array($option) ? ($option['value'] ?? null) : $option,
            ])
            ->values()
            ->all();
    }

    private function hiddenOptionKeys(): array
    {
        return collect($this->config('options') ?? [])
            ->filter(fn ($option) => is_array($option) && ($option['hidden'] ?? false) === true)
            ->map(fn ($option, $key) => (string) ($option['key'] ?? $key))
            ->values()
            ->all();
    }

    public function preProcessRenderable(array $data): array
    {
        $hidden = $this->hiddenOptionKeys();

        $data['options'] = collect($data['options'] ?? [])
            ->reject(fn ($label, $key) => in_array((string) $key, $hidden, true))
            ->all();

        return $data;
    }

    private function hideableChartOptions(FieldResponses $responses, ?string $icon = null): Collection
    {
        $hidden = $this->hiddenOptionKeys();
        $counts = $responses->counts();

        return collect($this->toField()->fieldtype()->preload()['options'])
            ->reject(fn ($option) => in_array((string) $option['value'], $hidden, true) && ! $counts->get($option['value']))
            ->map(fn ($option) => new ChartOption($option['value'], $option['label'], icon: $icon))
            ->values();
    }
}

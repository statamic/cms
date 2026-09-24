<?php

namespace Statamic\Forms\Charts;

use Illuminate\Support\Collection;
use Statamic\Forms\Fields\FormField;
use Statamic\Forms\Insights\Insight;

/** @internal */
final readonly class SummaryChart
{
    public function __construct(
        private FormField $field,
        private Chart $chart,
        private Collection $insights,
        private bool $usesDefaultInsights,
    ) {
    }

    public static function fromLayout(FormField $field, array $layout = []): ?self
    {
        if (! $chart = self::resolveChart($field, $layout['chart'] ?? null)) {
            return null;
        }

        $usesDefaultInsights = ! is_array($layout['insights'] ?? null);

        $fieldtype = $field->fieldtype();

        $insights = ($usesDefaultInsights ? collect($fieldtype->defaultInsights()) : self::insightClasses($layout['insights']))
            ->map(fn (string $class): Insight => app($class)->setConfig($fieldtype->insightConfig()))
            ->filter(fn (Insight $insight): bool => $insight->appliesTo($field))
            ->values();

        return new self($field, $chart, $insights, $usesDefaultInsights);
    }

    private static function resolveChart(FormField $field, mixed $handle): ?Chart
    {
        if (is_string($handle) && ($class = app('statamic.form-charts')->get($handle))) {
            $chart = app($class);

            if ($chart->appliesTo($field)) {
                return $chart;
            }
        }

        $class = $field->fieldtype()->defaultChart();

        return $class ? app($class) : null;
    }

    private static function insightClasses(array $items): Collection
    {
        return collect($items)
            ->map(fn ($item) => is_array($item) && is_string($type = $item['type'] ?? null)
                ? app('statamic.form-insights')->get($type)
                : null)
            ->filter()
            ->unique()
            ->values();
    }

    public function field(): FormField
    {
        return $this->field;
    }

    public function chart(): Chart
    {
        return $this->chart;
    }

    public function insights(): Collection
    {
        return $this->insights;
    }

    public function layout(): array
    {
        $layout = [
            'field' => $this->field->handle(),
            'chart' => $this->chart::handle(),
        ];

        if (! $this->usesDefaultInsights) {
            $layout['insights'] = $this->insights
                ->map(fn (Insight $insight): array => ['type' => $insight::handle()])
                ->values()
                ->all();
        }

        return $layout;
    }
}

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
    ) {
    }

    public static function fromLayout(FormField $field, array $layout = []): ?self
    {
        if (! $chart = self::resolveChart($field, $layout['chart'] ?? null)) {
            return null;
        }

        $fieldtype = $field->fieldtype();

        $insights = collect($fieldtype->defaultInsights())
            ->map(fn (string $class): Insight => app($class)->setConfig($fieldtype->insightConfig()))
            ->filter(fn (Insight $insight): bool => $insight->appliesTo($field))
            ->values();

        return new self($field, $chart, $insights);
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
}

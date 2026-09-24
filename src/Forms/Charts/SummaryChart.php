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

        $stored = $layout['insights'] ?? null;

        $insights = is_array($stored) ? self::resolveInsights($field, self::insightClasses($stored)) : collect();

        // A list where nothing resolves (e.g. an uninstalled addon's insight) isn't a choice of no insights.
        $usesDefaultInsights = ! is_array($stored) || ($stored !== [] && $insights->isEmpty());

        if ($usesDefaultInsights) {
            $insights = self::resolveInsights($field, collect($field->fieldtype()->defaultInsights()));
        }

        return new self($field, $chart, $insights, $usesDefaultInsights);
    }

    private static function resolveInsights(FormField $field, Collection $classes): Collection
    {
        $config = $field->fieldtype()->insightConfig();

        return $classes
            ->map(fn (string $class): Insight => app($class)->setConfig($config))
            ->filter(fn (Insight $insight): bool => $insight->appliesTo($field))
            ->values();
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

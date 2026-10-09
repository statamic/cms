<?php

namespace Statamic\Http\Controllers\CP\Forms;

use Illuminate\Support\Collection;
use Statamic\Contracts\Forms\SubmissionQueryBuilder;
use Statamic\Facades\User;
use Statamic\Forms\Charts\SummaryChart;
use Statamic\Forms\Fields\FormField;
use Statamic\Forms\Insights\Insight;
use Statamic\Forms\Summary\FieldResponseCounter;
use Statamic\Forms\Summary\FieldResponses;
use Statamic\Http\Controllers\CP\CpController;
use Statamic\Http\Controllers\CP\Forms\Concerns\QueriesFormSubmissionSearch;
use Statamic\Http\Requests\FilteredRequest;
use Statamic\Query\Scopes\Filters\Concerns\QueriesFilters;
use Statamic\Statamic;
use Statamic\Support\Arr;

class FormSummaryController extends CpController
{
    use QueriesFilters, QueriesFormSubmissionSearch;

    public function __invoke(FilteredRequest $request, $form): array
    {
        abort_unless(Statamic::formsProInstalled(), 404);

        $this->authorize('viewSubmissions', $form);

        $this->decodeCharts($request);

        $request->validate([
            'charts' => 'nullable|array',
            'charts.*.field' => 'required|string|distinct',
            'charts.*.chart' => 'required|string',
            'charts.*.insights' => 'nullable|array',
            'charts.*.insights.*' => 'array',
            'charts.*.insights.*.type' => 'required|string',
        ]);

        $numbers = $this->fieldNumbers($form);
        $charts = $this->resolveCharts($request, $form);

        [$total, $responses] = $this->countResponses($this->query($request, $form), $charts);

        return [
            'total' => $total,
            'fields' => $charts
                ->map(fn (SummaryChart $summary): array => $this->summarizeField(
                    $summary,
                    $responses[$summary->field()->handle()],
                    $numbers->get($summary->field()->handle())
                ))
                ->values(),
            'meta' => $this->meta($form),
        ];
    }

    // The layout is sent encoded because a query string can't represent an empty insights list.
    private function decodeCharts(FilteredRequest $request): void
    {
        $charts = $request->input('charts');

        // Like filters, anything that isn't an encoded layout is ignored.
        $request->merge(['charts' => is_string($charts) ? json_decode(base64_decode($charts), true) : null]);
    }

    private function resolveCharts(FilteredRequest $request, $form): Collection
    {
        $fields = $this->visibleFields($form);
        $layout = $request->input('charts') ?? $form->charts();

        if (is_null($layout)) {
            return $fields
                ->filter(fn (FormField $field): bool => $field->fieldtype()->defaultChart() !== null)
                ->map(fn (FormField $field): ?SummaryChart => SummaryChart::fromLayout($field))
                ->filter()
                ->values();
        }

        return collect($layout)
            ->map(function ($config) use ($fields): ?SummaryChart {
                if (! $field = $fields->get(Arr::get($config, 'field'))) {
                    return null;
                }

                return SummaryChart::fromLayout($field, $config);
            })
            ->filter()
            ->values();
    }

    private function visibleFields($form): Collection
    {
        return $form->formFields()->fields()
            ->reject(fn (FormField $field): bool => $field->config()['hidden'] ?? false);
    }

    private function fieldNumbers($form): Collection
    {
        return $this->visibleFields($form)
            ->filter(fn (FormField $field): bool => $field->fieldtype()->collectsValue())
            ->values()
            ->mapWithKeys(fn (FormField $field, int $index): array => [$field->handle() => $index + 1]);
    }

    private function query(FilteredRequest $request, $form): SubmissionQueryBuilder
    {
        $query = $form->querySubmissions();

        $this->queryFilters($query, $request->filters, ['form' => $form->handle()]);

        $this->applySubmissionSearch($query, $form, $request->input('search'));

        return $query;
    }

    private function countResponses(SubmissionQueryBuilder $query, Collection $charts): array
    {
        $total = 0;
        $counters = $charts
            ->mapWithKeys(fn (SummaryChart $summary) => [$summary->field()->handle() => new FieldResponseCounter($summary->field())])
            ->all();

        foreach ($query->lazy(500) as $submission) {
            $total++;

            foreach ($counters as $handle => $counter) {
                $counter->add($submission->get($handle));
            }
        }

        return [$total, array_map(fn (FieldResponseCounter $counter) => $counter->responses(), $counters)];
    }

    private function summarizeField(SummaryChart $summary, FieldResponses $responses, ?int $number): array
    {
        $field = $summary->field();
        $fieldtype = $field->fieldtype();
        $chart = $summary->chart();

        return [
            'handle' => $field->handle(),
            'display' => $field->display(),
            'icon' => $fieldtype->icon(),
            'fieldtype' => $fieldtype->handle(),
            'number' => $number,
            'responses' => $responses->total(),
            'chart' => [
                'handle' => $chart::handle(),
                'component' => $chart->component(),
                'props' => $chart->props($responses, $fieldtype->chartOptions($responses)),
            ],
            'insights' => $summary->insights()
                ->map(fn (Insight $insight): array => [
                    'handle' => $insight::handle(),
                    'component' => $insight->component(),
                    'props' => $insight->props($responses),
                ])
                ->values(),
            'layout' => $summary->layout(),
        ];
    }

    private function meta($form): array
    {
        if (! User::current()->can('edit', $form)) {
            return [];
        }

        return [
            'charts' => app('statamic.form-charts')
                ->map(function (string $class): array {
                    $chart = app($class);

                    return [
                        'handle' => $chart::handle(),
                        'title' => $chart::title(),
                        'icon' => $chart->icon(),
                        'component' => $chart->component(),
                    ];
                })
                ->values(),
            'fields' => $this->visibleFields($form)
                ->filter(fn (FormField $field): bool => $field->fieldtype()->defaultChart() !== null)
                ->map(fn (FormField $field): array => [
                    'handle' => $field->handle(),
                    'display' => $field->display(),
                    'icon' => $field->fieldtype()->icon(),
                    'default_chart' => $field->fieldtype()->defaultChart()::handle(),
                    'charts' => app('statamic.form-charts')
                        ->filter(fn (string $class): bool => app($class)->appliesTo($field))
                        ->keys()
                        ->values(),
                ])
                ->values(),
        ];
    }
}

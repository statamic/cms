<?php

namespace Statamic\Http\Controllers\CP\Forms;

use Illuminate\Support\Collection;
use Statamic\Contracts\Forms\SubmissionQueryBuilder;
use Statamic\Facades\User;
use Statamic\Forms\Charts\SummaryChart;
use Statamic\Forms\Fields\FormField;
use Statamic\Forms\Insights\Insight;
use Statamic\Http\Controllers\CP\CpController;
use Statamic\Http\Controllers\CP\Forms\Concerns\QueriesFormSubmissionSearch;
use Statamic\Http\Requests\FilteredRequest;
use Statamic\Query\Scopes\Filters\Concerns\QueriesFilters;
use Statamic\Statamic;

class FormSummaryController extends CpController
{
    use QueriesFilters, QueriesFormSubmissionSearch;

    public function __invoke(FilteredRequest $request, $form): array
    {
        abort_unless(Statamic::formsProInstalled(), 404);

        $this->authorize('viewSubmissions', $form);

        $charts = $form->summaryCharts();
        $numbers = $this->fieldNumbers($form);

        [$total, $values] = $this->collectValues($this->query($request, $form), $charts);

        return [
            'total' => $total,
            'fields' => $charts
                ->map(fn (SummaryChart $summary): array => $this->summarizeField(
                    $summary,
                    $values[$summary->field()->handle()],
                    $numbers->get($summary->field()->handle())
                ))
                ->values(),
            'meta' => $this->meta($form),
        ];
    }

    private function fieldNumbers($form): Collection
    {
        return $form->formFields()->fields()
            ->reject(fn (FormField $field): bool => $field->config()['hidden'] ?? false)
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

    private function collectValues(SubmissionQueryBuilder $query, Collection $charts): array
    {
        $total = 0;
        $values = $charts->mapWithKeys(fn (SummaryChart $summary) => [$summary->field()->handle() => collect()]);

        foreach ($query->lazy(500) as $submission) {
            $total++;

            $values->each(function (Collection $collected, string $handle) use ($submission) {
                if (filled($value = $submission->get($handle))) {
                    $collected->put($submission->id(), $value);
                }
            });
        }

        return [$total, $values];
    }

    private function summarizeField(SummaryChart $summary, Collection $values, ?int $number): array
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
            'responses' => $values->count(),
            'chart' => [
                'handle' => $chart::handle(),
                'component' => $chart->component(),
                'props' => $summary->props($values),
            ],
            'insights' => collect($fieldtype->insights())
                ->map(fn (Insight $insight): array => [
                    'handle' => $insight::handle(),
                    'component' => $insight->component(),
                    'props' => $insight->props($values),
                ])
                ->values(),
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
            'fields' => $form->formFields()->fields()
                ->reject(fn (FormField $field): bool => $field->config()['hidden'] ?? false)
                ->filter(fn (FormField $field): bool => $field->fieldtype()->defaultChart() !== null)
                ->map(fn (FormField $field): array => [
                    'handle' => $field->handle(),
                    'display' => $field->display(),
                    'icon' => $field->fieldtype()->icon(),
                    'default_chart' => $field->fieldtype()->defaultChart()::handle(),
                ])
                ->values(),
        ];
    }
}

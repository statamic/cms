<?php

namespace Statamic\Http\Controllers\CP\Forms;

use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Statamic\Forms\Fields\FormField;
use Statamic\Http\Controllers\CP\CpController;
use Statamic\Statamic;

use function Statamic\trans as __;

class UpdateFormChartsController extends CpController
{
    public function __invoke(Request $request, $form)
    {
        abort_unless(Statamic::formsProInstalled(), 404);

        $this->authorize('edit', $form);

        $request->validate([
            'charts' => 'present|array',
            'charts.*.field' => 'required|string|distinct',
            'charts.*.chart' => 'required|string',
            'charts.*.insights' => 'nullable|array',
            'charts.*.insights.*' => 'array',
            'charts.*.insights.*.type' => 'required|string',
        ]);

        $charts = collect($request->input('charts'))
            ->map(fn (array $config): array => $this->chartConfig($config))
            ->each(fn ($config) => $this->validateChart($form, $config));

        $form->charts($charts->values()->all())->save();

        return response()->noContent();
    }

    private function chartConfig(array $config): array
    {
        $chart = [
            'field' => $config['field'],
            'chart' => $config['chart'],
        ];

        if (isset($config['insights'])) {
            $chart['insights'] = collect($config['insights'])
                ->map(fn (array $insight): array => ['type' => $insight['type']])
                ->values()
                ->all();
        }

        return $chart;
    }

    private function validateChart($form, array $config): void
    {
        $field = $form->formFields()->field($config['field']);

        if (! $field) {
            throw ValidationException::withMessages([
                'charts' => __('statamic::validation.form_chart_unknown_field', ['field' => $config['field']]),
            ]);
        }

        if ($field->config()['hidden'] ?? false) {
            throw ValidationException::withMessages([
                'charts' => __('statamic::validation.form_chart_hidden_field', ['field' => $config['field']]),
            ]);
        }

        if (! app('statamic.form-charts')->has($config['chart'])) {
            throw ValidationException::withMessages([
                'charts' => __('statamic::validation.form_chart_unknown_chart', ['chart' => $config['chart']]),
            ]);
        }

        if (! app(app('statamic.form-charts')->get($config['chart']))->appliesTo($field)) {
            throw ValidationException::withMessages([
                'charts' => __('statamic::validation.form_chart_not_applicable', ['chart' => $config['chart'], 'field' => $config['field']]),
            ]);
        }

        $this->validateInsights($field, $config);
    }

    private function validateInsights(FormField $field, array $config): void
    {
        $seen = [];

        foreach ($config['insights'] ?? [] as ['type' => $type]) {
            if (! $class = app('statamic.form-insights')->get($type)) {
                throw ValidationException::withMessages([
                    'charts' => __('statamic::validation.form_insight_unknown', ['insight' => $type]),
                ]);
            }

            if (in_array($type, $seen, true)) {
                throw ValidationException::withMessages([
                    'charts' => __('statamic::validation.form_insight_duplicate', ['insight' => $type, 'field' => $config['field']]),
                ]);
            }

            $seen[] = $type;

            if (! app($class)->setConfig($field->fieldtype()->insightConfig())->appliesTo($field)) {
                throw ValidationException::withMessages([
                    'charts' => __('statamic::validation.form_insight_not_applicable', ['insight' => $type, 'field' => $config['field']]),
                ]);
            }
        }
    }
}

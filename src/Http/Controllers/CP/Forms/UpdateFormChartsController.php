<?php

namespace Statamic\Http\Controllers\CP\Forms;

use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
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
            'charts.*.field' => 'required|string',
            'charts.*.chart' => 'required|string',
        ]);

        $charts = collect($request->input('charts'))
            ->map(fn (array $config): array => [
                'field' => $config['field'],
                'chart' => $config['chart'],
            ])
            ->each(fn ($config) => $this->validateChart($form, $config));

        $form->charts($charts->values()->all())->save();

        return response()->noContent();
    }

    private function validateChart($form, array $config): void
    {
        if (! $form->formFields()->field($config['field'])) {
            throw ValidationException::withMessages([
                'charts' => __('statamic::validation.form_chart_unknown_field', ['field' => $config['field']]),
            ]);
        }

        if (! app('statamic.form-charts')->has($config['chart'])) {
            throw ValidationException::withMessages([
                'charts' => __('statamic::validation.form_chart_unknown_chart', ['chart' => $config['chart']]),
            ]);
        }
    }
}

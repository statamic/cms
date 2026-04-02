<?php

namespace Statamic\Http\Controllers\CP\Forms;

use Statamic\Exceptions\NotFoundHttpException;
use Statamic\Http\Controllers\CP\CpController;
use Statamic\Http\Controllers\CP\Forms\Concerns\QueriesFormSubmissionSearch;
use Statamic\Http\Requests\FilteredRequest;
use Statamic\Query\Scopes\Filters\Concerns\QueriesFilters;

class FormExportController extends CpController
{
    use QueriesFilters;
    use QueriesFormSubmissionSearch;

    public function export(FilteredRequest $request, $form, $type)
    {
        $this->authorize('view', $form);

        if (! $exporter = $form->exporter($type)) {
            throw new NotFoundHttpException;
        }

        if ($this->shouldApplyFilteredScope($request)) {
            $exporter->setSubmissions($this->getScopedSubmissions($request, $form));
        }

        return $this->request->has('download') ? $exporter->download() : $exporter->response();
    }

    private function shouldApplyFilteredScope(FilteredRequest $request): bool
    {
        return collect(['filters', 'search', 'sort', 'order'])->contains(function ($parameter) use ($request) {
            return filled($request->input($parameter));
        });
    }

    private function getScopedSubmissions(FilteredRequest $request, $form)
    {
        $query = $form->querySubmissions();

        $this->queryFilters($query, $request->filters, [
            'form' => $form->handle(),
        ]);

        $this->applySubmissionSearch($query, $form, $request->search);

        if ($sortField = $request->input('sort')) {
            $sortDirection = $request->input('order', $sortField === 'date' ? 'desc' : 'asc');
            $query->orderBy($sortField, $sortDirection);
        }

        return $query->get();
    }
}

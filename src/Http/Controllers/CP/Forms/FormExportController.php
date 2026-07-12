<?php

namespace Statamic\Http\Controllers\CP\Forms;

use Statamic\Exceptions\NotFoundHttpException;
use Statamic\Http\Controllers\CP\CpController;
use Statamic\Http\Controllers\CP\Forms\Concerns\QueriesFormSubmissionSearch;
use Statamic\Http\Requests\FilteredRequest;
use Statamic\Query\OrderBy;
use Statamic\Query\Scopes\Filters\Concerns\QueriesFilters;

class FormExportController extends CpController
{
    use QueriesFilters, QueriesFormSubmissionSearch;

    public function export(FilteredRequest $request, $form, $type)
    {
        $this->authorize('view', $form);

        if (! $exporter = $form->exporter($type)) {
            throw new NotFoundHttpException;
        }

        if ($this->shouldApplyFilteredScope($request)) {
            $exporter->setSubmissions($this->getScopedSubmissions($request, $form));
        }

        return $request->has('download') ? $exporter->download() : $exporter->response();
    }

    protected function shouldApplyFilteredScope(FilteredRequest $request)
    {
        return $request->has('filters') || $request->has('search') || $request->has('sort') || $request->has('order');
    }

    protected function getScopedSubmissions(FilteredRequest $request, $form)
    {
        $query = $form->querySubmissions();

        $this->queryFilters($query, $request->filters, [
            'form' => $form->handle(),
        ]);

        $this->applySubmissionSearch($query, $form, $request->input('search'));

        if ($sort = OrderBy::column($request->input('sort'))) {
            $query->orderBy($sort, $request->input('order', $sort === 'date' ? 'desc' : 'asc'));
        }

        return $query->get();
    }
}

<?php

namespace Statamic\Http\Controllers\CP\Forms;

use Statamic\API\Form;
use Statamic\CP\Column;
use Statamic\API\Config;
use Statamic\API\Helper;
use Statamic\Http\Controllers\CP\CpController;
use Statamic\Forms\Presenters\UploadedFilePresenter;
use Statamic\Extensions\Pagination\LengthAwarePaginator;

class FormSubmissionsController extends CpController
{
    public function index($form)
    {
        $form = Form::get($form);

        $this->authorize('view', $form);

        $columns = $form->columns()
            ->setPreferred("forms.{$form->handle()}.columns")
            ->ensurePrepended(Column::make('datestamp')->label('date')->value('datestring'))
            ->rejectUnlisted()
            ->values();

        $submissions = $form->submissions()->map(function ($submission) use ($form) {
            $this->sanitizeSubmission($submission);

            return array_merge($submission->toArray(), [
                'datestring' => $submission->date()->format($form->dateFormat()),
                'datestamp' => $submission->date()->timestamp,
                'url' => cp_route('forms.submissions.show', [$form->name(), $submission->id()]),
                'deleteable' => me()->can('delete', $submission),
            ]);
        });

        // Set the default/fallback sort order
        $sort = 'datestamp';
        $sortOrder = 'asc';

        // Custom sorting will override anything predefined.
        if ($customSort = $this->request->sort) {
            $sort = $customSort;
        }
        if ($customOrder = $this->request->order) {
            $sortOrder = $customOrder;
        }

        // Perform the sort!
        $submissions = $submissions->sortBy($sort, null, $sortOrder === 'desc');

        // Set up the paginator, since we don't want to display all the entries.
        $totalSubmissionCount = $submissions->count();
        $perPage = Config::get('statamic.cp.pagination_size');
        $currentPage = (int) $this->request->page ?: 1;
        $offset = ($currentPage - 1) * $perPage;
        $submissions = $submissions->slice($offset, $perPage);
        $paginator = new LengthAwarePaginator($submissions, $totalSubmissionCount, $perPage, $currentPage);

        return [
            'data' => $submissions->values(),
            'meta' => [
                'columns' => $columns,
                'sortColumn' => $sort,
            ]
            // 'pagination' => [
            //     'totalItems' => $totalSubmissionCount,
            //     'itemsPerPage' => $perPage,
            //     'totalPages'    => $paginator->lastPage(),
            //     'currentPage'   => $paginator->currentPage(),
            //     'prevPage'      => $paginator->previousPageUrl(),
            //     'nextPage'      => $paginator->nextPageUrl(),
            //     'segments'      => array_get($paginator->renderArray(), 'segments')
            // ]
        ];
    }

    private function sanitizeSubmission($submission)
    {
        collect($submission->data())->each(function ($value, $field) use ($submission) {
            $sanitized = ($submission->formset()->isUploadableField($field))
                ? UploadedFilePresenter::render($submission, $field)
                : $this->sanitizeField($value, $submission);

            $submission->set($field, $sanitized);
        });
    }

    private function sanitizeField($value, $submission)
    {
        $is_arr = is_array($value);

        $values = (array) $value;

        foreach ($values as &$value) {
            if (is_array($value)) {
                $value = json_encode($value);
            } elseif (! $submission->formset()->get('sanitize', true)) {
                $value = sanitize($value);
            }
        }

        return ($is_arr) ? $values : $values[0];
    }

    public function destroy($form, $id)
    {
        $submission = Form::get($form)->submission($id);

        $this->authorize('delete', $submission);

        $submission->delete();

        return response('', 204);
    }

    public function show($form, $submission)
    {
        $form = Form::get($form);

        if (! $submission = $form->submission($submission)) {
            return $this->pageNotFound();
        }

        $this->authorize('view', $submission);

        $this->sanitizeSubmission($submission);

        return view('statamic::forms.submission', compact('form', 'submission'));
    }
}

<?php

namespace Statamic\Http\Controllers\CP\Forms;

use Statamic\Facades\Form;
use Statamic\CP\Column;
use Statamic\Facades\Config;
use Statamic\Facades\Helper;
use Illuminate\Http\Resources\Json\Resource;
use Statamic\Facades\User;
use Statamic\Http\Controllers\CP\CpController;
use Statamic\Forms\Presenters\UploadedFilePresenter;
use Statamic\Extensions\Pagination\LengthAwarePaginator;

class FormSubmissionsController extends CpController
{
    public function index($form)
    {
        $this->authorize('view', $form);

        if (! $form->blueprint()) {
            return ['data' => [], 'meta' => ['columns' => []]];
        }

        $columns = $form->blueprint()->columns()
            ->setPreferred("forms.{$form->handle()}.columns")
            ->ensurePrepended(Column::make('datestamp')->label('date')->value('datestring'))
            ->rejectUnlisted()
            ->values();

        $submissions = $form->submissions()->map(function ($submission) use ($form) {
            $this->sanitizeSubmission($submission);

            return array_merge($submission->toArray(), [
                'datestring' => $submission->date()->format($form->dateFormat()),
                'datestamp' => $submission->date()->timestamp,
                'url' => cp_route('forms.submissions.show', [$form->handle(), $submission->id()]),
                'deleteable' => User::current()->can('delete', $submission),
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
        $perPage = request('perPage') ?? Config::get('statamic.cp.pagination_size');
        $currentPage = (int) $this->request->page ?: 1;
        $offset = ($currentPage - 1) * $perPage;
        $submissions = $submissions->slice($offset, $perPage);
        $paginator = new LengthAwarePaginator($submissions, $totalSubmissionCount, $perPage, $currentPage);

        return Resource::collection($paginator)->additional(['meta' => [
            'columns' => $columns,
            'sortColumn' => $sort,
        ]]);
    }

    private function sanitizeSubmission($submission)
    {
        collect($submission->data())->each(function ($value, $field) use ($submission) {
            $sanitized = ($submission->form()->isUploadableField($field))
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
            } elseif (! $submission->form()->sanitize()) {
                $value = sanitize($value);
            }
        }

        return ($is_arr) ? $values : $values[0];
    }

    public function destroy($form, $id)
    {
        $submission = $form->submission($id);

        $this->authorize('delete', $submission);

        $submission->delete();

        return response('', 204);
    }

    public function show($form, $submission)
    {
        if (! $submission = $form->submission($submission)) {
            return $this->pageNotFound();
        }

        $this->authorize('view', $submission);

        $this->sanitizeSubmission($submission);

        return view('statamic::forms.submission', compact('form', 'submission'));
    }
}

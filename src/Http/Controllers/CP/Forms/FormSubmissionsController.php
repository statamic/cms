<?php

namespace Statamic\Http\Controllers\CP\Forms;

use Illuminate\Http\Resources\Json\Resource;
use Statamic\CP\Column;
use Statamic\Extensions\Pagination\LengthAwarePaginator;
use Statamic\Facades\Config;
use Statamic\Facades\Form;
use Statamic\Facades\Helper;
use Statamic\Facades\User;
use Statamic\Forms\Presenters\UploadedFilePresenter;
use Statamic\Http\Controllers\CP\CpController;
use Statamic\Http\Resources\CP\Submissions\Submissions;
use Statamic\Support\Str;

class FormSubmissionsController extends CpController
{
    public function index($form)
    {
        $this->authorize('view', $form);

        if (! $form->blueprint()) {
            return ['data' => [], 'meta' => ['columns' => []]];
        }

        // Get sanitized submissions.
        $submissions = $form->submissions()->each(function ($submission) {
            $this->sanitizeSubmission($submission);
        })->values();

        // Search submissions.
        if ($search = $this->request->search) {
            $submissions = $this->searchSubmissions($submissions);
        }

        // Sort submissions.
        $sort = $this->request->sort ?? 'datestamp';
        $order = $this->request->order ?? ($sort === 'datestamp' ? 'desc' : 'asc');
        $submissions = $this->sortSubmissions($submissions, $sort, $order);

        // Paginate submissions.
        $totalSubmissionCount = $submissions->count();
        $perPage = request('perPage') ?? Config::get('statamic.cp.pagination_size');
        $currentPage = (int) $this->request->page ?: 1;
        $offset = ($currentPage - 1) * $perPage;
        $submissions = $submissions->slice($offset, $perPage);
        $paginator = new LengthAwarePaginator($submissions, $totalSubmissionCount, $perPage, $currentPage);

        return (new Submissions($paginator))
            ->blueprint($form->blueprint())
            ->columnPreferenceKey("forms.{$form->handle()}.columns")
            ->additional(['meta' => [
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

    private function searchSubmissions($submissions)
    {
        return $submissions->filter(function ($submission) {
            return collect($submission->data())
                ->filter(function ($value) {
                    return $value && is_string($value);
                })
                ->filter(function ($value) {
                    return Str::contains(strtolower($value), strtolower($this->request->search));
                })
                ->isNotEmpty();
        })->values();
    }

    private function sortSubmissions($submissions, $sortBy, $sortOrder)
    {
        return $submissions->sortBy(function ($submission) use ($sortBy) {
            return $sortBy === 'datestamp'
                ? $submission->date()->timestamp
                : $submission->get($sortBy);
        }, null, $sortOrder === 'desc')->values();
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

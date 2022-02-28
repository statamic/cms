<?php

namespace Statamic\Http\Controllers\CP\Forms;

use Statamic\Extensions\Pagination\LengthAwarePaginator;
use Statamic\Facades\Config;
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

        $submissions = $form->submissions()->values();

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
            ->columnPreferenceKey("forms.{$form->handle()}.columns");
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

        $blueprint = $submission->blueprint();
        $fields = $blueprint->fields()->addValues($submission->data()->all())->preProcess();

        return view('statamic::forms.submission', [
            'form' => $form,
            'submission' => $submission,
            'blueprint' => $blueprint->toPublishArray(),
            'values' => $fields->values(),
            'meta' => $fields->meta(),
            'title' => $submission->date()->format('M j, Y @ H:i'),
        ]);
    }
}

<?php

namespace Statamic\Http\Controllers\CP\Forms;

use Statamic\Extensions\Pagination\LengthAwarePaginator;
use Statamic\Facades\Config;
use Statamic\Fields\Field;
use Statamic\Http\Controllers\CP\CpController;
use Statamic\Http\Requests\FilteredRequest;
use Statamic\Http\Resources\CP\Submissions\Submissions;
use Statamic\Query\Scopes\Filters\Concerns\QueriesFilters;

class FormSubmissionsController extends CpController
{
    use QueriesFilters;

    public function index(FilteredRequest $request, $form)
    {
        $this->authorize('view', $form);

        if (! $form->blueprint()) {
            return ['data' => [], 'meta' => ['columns' => []]];
        }

        $query = $form->querySubmissions();

        if ($search = request('search')) {
            $query->where('date', 'like', '%'.$search.'%');

            $form->blueprint()->fields()->all()
                ->filter(function (Field $field): bool {
                    return in_array($field->type(), ['text', 'textarea', 'integer']);
                })
                ->each(function (Field $field) use ($query, $search): void {
                    $query->orWhere($field->handle(), 'like', '%'.$search.'%');
                });
        }

        $activeFilterBadges = $this->queryFilters($query, $request->filters, [
            'form' => $form->handle(),
        ]);

        $sortField = request('sort', 'date');
        $sortDirection = request('order', $sortField === 'date' ? 'desc' : 'asc');

        if ($sortField) {
            $query->orderBy($sortField, $sortDirection);
        }

        $submissions = $query->get();

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
                'activeFilterBadges' => $activeFilterBadges,
            ]]);
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
            'title' => $submission->formattedDate(),
        ]);
    }
}

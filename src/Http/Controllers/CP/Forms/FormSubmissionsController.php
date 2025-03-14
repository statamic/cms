<?php

namespace Statamic\Http\Controllers\CP\Forms;

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

        $query = $this->indexQuery($form);

        $activeFilterBadges = $this->queryFilters($query, $request->filters, [
            'form' => $form->handle(),
        ]);

        $sortField = request('sort', 'date');
        $sortDirection = request('order', $sortField === 'date' ? 'desc' : 'asc');

        if ($sortField) {
            $query->orderBy($sortField, $sortDirection);
        }

        $submissions = $query->paginate(request('perPage'));

        return (new Submissions($submissions))
            ->blueprint($form->blueprint())
            ->columnPreferenceKey("forms.{$form->handle()}.columns")
            ->additional(['meta' => [
                'activeFilterBadges' => $activeFilterBadges,
            ]]);
    }

    protected function indexQuery($form)
    {
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

        return $query;
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
        ]);
    }
}

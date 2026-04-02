<?php

namespace Statamic\Http\Controllers\CP\Forms;

use Illuminate\Http\Request;
use Inertia\Inertia;
use Statamic\Events\FormSubmitted;
use Statamic\Facades\Site;
use Statamic\Forms\FakeSubmissionGenerator;
use Statamic\Forms\SendEmails;
use Statamic\Http\Controllers\CP\CpController;
use Statamic\Http\Controllers\CP\Forms\Concerns\QueriesFormSubmissionSearch;
use Statamic\Http\Requests\FilteredRequest;
use Statamic\Http\Resources\CP\Submissions\Submissions;
use Statamic\Query\Scopes\Filters\Concerns\QueriesFilters;

class FormSubmissionsController extends CpController
{
    use QueriesFilters;
    use QueriesFormSubmissionSearch;

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

        $this->applySubmissionSearch($query, $form, request('search'));

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

        return Inertia::render('forms/Submission', [
            'id' => $submission->id(),
            'formTitle' => $form->title(),
            'date' => $submission->date()->toIso8601String(),
            'blueprint' => $blueprint->toPublishArray(),
            'values' => $fields->values(),
            'meta' => $fields->meta(),
        ]);
    }

    public function generateFake(Request $request, $form, FakeSubmissionGenerator $generator)
    {
        $this->authorize('view', $form);

        if (! $form->get('generate_fake_submissions', true)) {
            return response([
                'message' => __('statamic::messages.form_fake_submission_generation_disabled'),
            ], 403);
        }

        $validated = $request->validate([
            'mode' => ['required', 'in:cp_only,full_pipeline'],
        ]);

        $values = $generator->generate($form);
        $fields = $form->blueprint()->fields()->addValues($values);
        $submission = $form->makeSubmission();
        $submission->data(
            $fields->process()->values()->merge([
                '_fake' => true,
            ])
        );

        if ($validated['mode'] === 'full_pipeline') {
            if (FormSubmitted::dispatch($submission) === false) {
                return response([
                    'message' => __('statamic::messages.form_fake_submission_cancelled'),
                ], 422);
            }
        }

        $submission->save();

        if ($validated['mode'] === 'full_pipeline') {
            SendEmails::dispatch($submission, Site::default());
        }

        return response([
            'id' => $submission->id(),
            'message' => __('statamic::messages.form_fake_submission_generated'),
        ]);
    }
}

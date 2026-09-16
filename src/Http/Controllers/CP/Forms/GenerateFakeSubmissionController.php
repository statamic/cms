<?php

namespace Statamic\Http\Controllers\CP\Forms;

use Illuminate\Http\Request;
use Statamic\Events\FormSubmitted;
use Statamic\Facades\Site;
use Statamic\Forms\FakeSubmissionGenerator;
use Statamic\Forms\SendEmails;
use Statamic\Http\Controllers\CP\CpController;

use function Statamic\trans as __;

class GenerateFakeSubmissionController extends CpController
{
    public function __invoke(Request $request, $form, FakeSubmissionGenerator $generator)
    {
        $this->authorize('generateFakeSubmissions', $form);

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
        $submission = $form->makeSubmission()->site(Site::selected());
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
            SendEmails::dispatch($submission, $submission->site());
        }

        return response([
            'id' => $submission->id(),
            'message' => __('statamic::messages.form_fake_submission_generated'),
        ]);
    }
}

<?php

namespace Statamic\Http\Controllers\CP\Forms;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Bus;
use Statamic\Events\FormSubmitted;
use Statamic\Facades\Site;
use Statamic\Forms\FakeSubmissionGenerator;
use Statamic\Http\Controllers\CP\CpController;

use function Statamic\trans as __;

class GenerateFakeSubmissionController extends CpController
{
    public function __invoke(Request $request, $form)
    {
        abort_unless(FakeSubmissionGenerator::enabled(), 404);

        $this->authorize('generateFakeSubmissions', $form);

        $validated = $request->validate([
            'mode' => ['required', 'in:cp_only,full_pipeline'],
        ]);

        $values = app(FakeSubmissionGenerator::class)->generate($form);
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
            foreach ($submission->connectionJobs() as $job) {
                rescue(fn () => Bus::dispatch($job));
            }
        }

        return response([
            'id' => $submission->id(),
            'message' => __('statamic::messages.form_fake_submission_generated'),
        ]);
    }
}

<?php

namespace Statamic\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Response;
use Illuminate\Support\MessageBag;
use Statamic\Contracts\Forms\Submission;
use Statamic\Events\Data\SubmissionCreated;
use Statamic\Events\Data\SubmissionCreating;
use Statamic\Exceptions\PublishException;
use Statamic\Exceptions\SilentFormFailureException;
use Statamic\Facades\Form;
use Statamic\Forms\SendEmails;
use Statamic\Support\Arr;
use Statamic\Support\Str;

class FormController extends Controller
{
    /**
     * Handle a form submission request.
     *
     * @return mixed
     */
    public function submit($form)
    {
        $data = request()->all();

        $params = collect($data)
            ->filter(function ($value, $key) {
                return Str::startsWith($key, '_');
            })
            ->all();

        $submission = $form->createSubmission();

        try {
            $submission->data($data);
            $submission->uploadFiles();

            // Allow addons to prevent the submission of the form, return
            // their own errors, and modify the submission.
            [$errors, $submission] = $this->runCreatingEvent($submission);
        } catch (PublishException $e) {
            return $this->formFailure($params, $e->getErrors(), $form->handle());
        } catch (SilentFormFailureException $e) {
            return $this->formSuccess($params, $submission);
        }

        if ($errors) {
            return $this->formFailure($params, $errors, $form->handle());
        }

        if ($form->store()) {
            $submission->save();
        }

        SubmissionCreated::dispatch($submission);
        SendEmails::dispatch($submission);

        return $this->formSuccess($params, $submission);
    }

    /**
     * The steps for a successful form submission.
     *
     * Used for actual success and by honeypot.
     *
     * @param array $params
     * @param Submission $submission
     * @return Response
     */
    private function formSuccess($params, $submission)
    {
        if (request()->ajax()) {
            return response([
                'success' => true,
                'submission' => $submission->data(),
            ]);
        }

        $redirect = Arr::get($params, '_redirect');

        $response = $redirect ? redirect($redirect) : back();

        session()->flash("form.{$submission->form()->handle()}.success", __('Submission successful.'));
        session()->flash('submission', $submission);

        return $response;
    }

    /**
     * The steps for a failed form submission.
     *
     * @param array $params
     * @param array $submission
     * @param string $form
     * @return Response|RedirectResponse
     */
    private function formFailure($params, $errors, $form)
    {
        if (request()->ajax()) {
            return response([
                'errors' => (new MessageBag($errors))->all(),
                'error' => collect($errors)->map(function ($errors, $field) {
                    return $errors[0];
                })->all(),
            ], 400);
        }

        $redirect = Arr::get($params, '_error_redirect');

        $response = $redirect ? redirect($redirect) : back();

        return $response->withInput()->withErrors($errors, 'form.'.$form);
    }

    /**
     * Run the `submission:creating` event.
     *
     * This allows the submission to be short-circuited before it gets saved and show errors.
     * Or, a the submission may be modified. Lastly, an addon could just 'do something'
     * here without modifying/stopping the submission.
     *
     * Expects an array of event responses (multiple listeners can listen for the same event).
     * Each response in the array should be another array with either an `errors` or `submission` key.
     *
     * @param  Submission $submission
     * @return array
     */
    private function runCreatingEvent($submission)
    {
        $errors = [];

        $responses = SubmissionCreating::dispatch($submission);

        foreach ($responses as $response) {
            // Ignore any non-arrays
            if (! is_array($response)) {
                continue;
            }

            // If the event returned errors, tack those onto the array.
            if ($response_errors = array_get($response, 'errors')) {
                $errors = array_merge($response_errors, $errors);
                continue;
            }

            // If the event returned a submission, we'll replace it with that.
            $submission = array_get($response, 'submission');
        }

        return [$errors, $submission];
    }
}

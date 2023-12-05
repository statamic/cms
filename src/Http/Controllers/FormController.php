<?php

namespace Statamic\Http\Controllers;

use Illuminate\Http\Response;
use Illuminate\Support\Facades\URL;
use Statamic\Contracts\Forms\Submission;
use Statamic\Events\FormSubmitted;
use Statamic\Events\SubmissionCreated;
use Statamic\Exceptions\SilentFormFailureException;
use Statamic\Facades\Form;
use Statamic\Facades\Site;
use Statamic\Forms\Exceptions\FileContentTypeRequiredException;
use Statamic\Forms\SendEmails;
use Statamic\Http\Requests\FrontendFormRequest;
use Statamic\Support\Arr;
use Statamic\Support\Str;

class FormController extends Controller
{
    /**
     * Handle a form submission request.
     *
     * @return mixed
     */
    public function submit(FrontendFormRequest $request, $form)
    {
        $site = Site::findByUrl(URL::previous()) ?? Site::default();
        $fields = $form->blueprint()->fields();
        $this->validateContentType($request, $form);
        $values = array_merge($request->all(), $assets = $request->assets());
        $params = collect($request->all())->filter(function ($value, $key) {
            return Str::startsWith($key, '_');
        })->all();

        $fields = $fields->addValues($values);

        $submission = $form->makeSubmission();

        try {
            throw_if(Arr::get($values, $form->honeypot()), new SilentFormFailureException);

            $values = array_merge($values, $submission->uploadFiles($assets));

            $submission->data(
                $fields->addValues($values)->process()->values()
            );

            // If any event listeners return false, we'll do a silent failure.
            // If they want to add validation errors, they can throw an exception.
            throw_if(FormSubmitted::dispatch($submission) === false, new SilentFormFailureException);
        } catch (SilentFormFailureException $e) {
            return $this->formSuccess($params, $submission, true);
        }

        if ($form->store()) {
            $submission->save();
        } else {
            // When the submission is saved, this same created event will be dispatched.
            // We'll also fire it here if submissions are not configured to be stored
            // so that developers may continue to listen and modify it as needed.
            SubmissionCreated::dispatch($submission);
        }

        SendEmails::dispatch($submission, $site);

        return $this->formSuccess($params, $submission);
    }

    private function validateContentType($request, $form)
    {
        $type = Str::before($request->headers->get('CONTENT_TYPE'), ';');

        if ($type !== 'multipart/form-data' && $form->hasFiles()) {
            throw new FileContentTypeRequiredException;
        }
    }

    /**
     * The steps for a successful form submission.
     *
     * Used for actual success and by honeypot.
     *
     * @param  array  $params
     * @param  Submission  $submission
     * @param  bool  $silentFailure
     * @return Response
     */
    private function formSuccess($params, $submission, $silentFailure = false)
    {
        $redirect = $this->formSuccessRedirect($params, $submission);

        if (request()->ajax() || request()->wantsJson()) {
            return response([
                'success' => true,
                'submission_created' => ! $silentFailure,
                'submission' => $submission->data(),
                'redirect' => $redirect,
            ]);
        }

        $response = $redirect ? redirect($redirect) : back();

        if (! \Statamic\Facades\URL::isExternal($redirect)) {
            session()->flash("form.{$submission->form()->handle()}.success", __('Submission successful.'));
            session()->flash("form.{$submission->form()->handle()}.submission_created", ! $silentFailure);
            session()->flash('submission', $submission);
        }

        return $response;
    }

    private function formSuccessRedirect($params, $submission)
    {
        if (! $redirect = Form::getSubmissionRedirect($submission)) {
            $redirect = Arr::get($params, '_redirect');
        }

        return $redirect;
    }
}

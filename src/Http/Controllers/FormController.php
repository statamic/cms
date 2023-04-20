<?php

namespace Statamic\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\MessageBag;
use Illuminate\Support\Traits\Localizable;
use Illuminate\Validation\ValidationException;
use Statamic\Contracts\Forms\Submission;
use Statamic\Events\FormSubmitted;
use Statamic\Events\SubmissionCreated;
use Statamic\Exceptions\SilentFormFailureException;
use Statamic\Facades\Site;
use Statamic\Forms\Exceptions\FileContentTypeRequiredException;
use Statamic\Forms\SendEmails;
use Statamic\Support\Arr;
use Statamic\Support\Str;

class FormController extends Controller
{
    use Localizable;

    /**
     * Handle a form submission request.
     *
     * @return mixed
     */
    public function submit(Request $request, $form)
    {
        $site = Site::findByUrl(URL::previous()) ?? Site::default();
        $fields = $form->blueprint()->fields();
        $this->validateContentType($request, $form);
        $values = array_merge($request->all(), $assets = $this->normalizeAssetsValues($fields, $request));
        $params = collect($request->all())->filter(function ($value, $key) {
            return Str::startsWith($key, '_');
        })->all();

        $fields = $fields->addValues($values);

        $submission = $form->makeSubmission();

        try {
            $this->withLocale($site->lang(), function () use ($fields) {
                $fields->validate($this->extraRules($fields));
            });

            throw_if(Arr::get($values, $form->honeypot()), new SilentFormFailureException);

            $values = array_merge($values, $submission->uploadFiles($assets));

            $submission->data(
                $fields->addValues($values)->process()->values()
            );

            // If any event listeners return false, we'll do a silent failure.
            // If they want to add validation errors, they can throw an exception.
            throw_if(FormSubmitted::dispatch($submission) === false, new SilentFormFailureException);
        } catch (ValidationException $e) {
            return $this->formFailure($params, $e->errors(), $form->handle());
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
        if (request()->ajax() || request()->wantsJson()) {
            return response([
                'success' => true,
                'submission_created' => ! $silentFailure,
                'submission' => $submission->data(),
            ]);
        }

        $redirect = Arr::get($params, '_redirect');

        $response = $redirect ? redirect($redirect) : back();

        session()->flash("form.{$submission->form()->handle()}.success", __('Submission successful.'));
        session()->flash("form.{$submission->form()->handle()}.submission_created", ! $silentFailure);
        session()->flash('submission', $submission);

        return $response;
    }

    /**
     * The steps for a failed form submission.
     *
     * @param  array  $params
     * @param  array  $submission
     * @param  string  $form
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

    protected function normalizeAssetsValues($fields, $request)
    {
        // The assets fieldtype is expecting an array, even for `max_files: 1`, but we don't want to force that on the front end.
        return $fields->all()
            ->filter(function ($field) {
                return $field->fieldtype()->handle() === 'assets' && request()->hasFile($field->handle());
            })
            ->map(function ($field) use ($request) {
                return Arr::wrap($request->file($field->handle()));
            })
            ->all();
    }

    protected function extraRules($fields)
    {
        $assetFieldRules = $fields->all()
            ->filter(function ($field) {
                return $field->fieldtype()->handle() === 'assets';
            })
            ->mapWithKeys(function ($field) {
                return [$field->handle().'.*' => 'file'];
            })
            ->all();

        return $assetFieldRules;
    }
}

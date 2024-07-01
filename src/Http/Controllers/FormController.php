<?php

namespace Statamic\Http\Controllers;

use Illuminate\Http\Response;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\MessageBag;
use Illuminate\Validation\ValidationException;
use Statamic\Contracts\Forms\Submission;
use Statamic\Events\FormSubmitted;
use Statamic\Events\SubmissionCreated;
use Statamic\Exceptions\SilentFormFailureException;
use Statamic\Facades\Asset;
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
        $values = $request->all();

        $fields->all()
            ->filter(fn ($field) => $field->fieldtype()->handle() === 'checkboxes')
            ->each(function ($field) use (&$values) {
                return Arr::set($values, $field->handle(), collect(Arr::get($values, $field->handle(), []))->filter(fn ($value) => $value !== null)->values()->all());
            });

        $values = array_merge($values, $assets = $request->assets());
        $params = collect($request->all())->filter(function ($value, $key) {
            return Str::startsWith($key, '_');
        })->all();

        $fields = $fields->addValues($values);

        $submission = $form->makeSubmission();

        try {
            throw_if(Arr::get($values, $form->honeypot()), new SilentFormFailureException);

            $uploadedAssets = $submission->uploadFiles($assets);

            $values = array_merge($values, $uploadedAssets);

            $submission->data(
                $fields->addValues($values)->process()->values()
            );

            // If any event listeners return false, we'll do a silent failure.
            // If they want to add validation errors, they can throw an exception.
            throw_if(FormSubmitted::dispatch($submission) === false, new SilentFormFailureException);
        } catch (ValidationException $e) {
            $this->removeUploadedAssets($uploadedAssets);

            return $this->formFailure($params, $e->errors(), $form->handle());
        } catch (SilentFormFailureException $e) {
            if (isset($uploadedAssets)) {
                $this->removeUploadedAssets($uploadedAssets);
            }

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

        if ($type !== 'multipart/form-data' && $form->hasFiles() && $request->assets()) {
            throw new FileContentTypeRequiredException;
        }
    }

    /**
     * The steps for a failed form submission.
     *
     * @param  array  $params
     * @param  array  $errors
     * @param  string  $form
     * @return Response|RedirectResponse
     */
    private function formFailure($params, $errors, $form)
    {
        $request = request();

        if ($request->ajax()) {
            return response([
                'errors' => (new MessageBag($errors))->all(),
                'error' => collect($errors)->map(function ($errors, $field) {
                    return $errors[0];
                })->all(),
            ], 400);
        }

        if ($request->isPrecognitive() || $request->wantsJson()) {
            throw ValidationException::withMessages($errors);
        }

        $redirect = Arr::get($params, '_error_redirect');

        $response = $redirect ? redirect($redirect) : back();

        return $response->withInput()->withErrors($errors, 'form.'.$form);
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

    /**
     * Remove any uploaded assets
     *
     * Triggered by a validation exception or silent failure
     */
    private function removeUploadedAssets(array $assets)
    {
        collect($assets)
            ->flatten()
            ->each(function ($id) {
                if ($asset = Asset::find($id)) {
                    $asset->delete();
                }
            });
    }
}

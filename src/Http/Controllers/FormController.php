<?php

namespace Statamic\Http\Controllers;

use Illuminate\Http\Response;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\MessageBag;
use Illuminate\Validation\ValidationException;
use Statamic\Contracts\Forms\Submission;
use Statamic\Exceptions\SilentFormFailureException;
use Statamic\Facades\Form;
use Statamic\Facades\Site;
use Statamic\Forms\Exceptions\FileContentTypeRequiredException;
use Statamic\Forms\SubmitForm;
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
        $this->validateContentType($request, $form);

        $params = collect($request->all())
            ->filter(fn ($value, string $key) => Str::startsWith($key, '_'))
            ->all();

        try {
            $submission = app(SubmitForm::class)(
                form: $form,
                data: $request->all(),
                files: $request->assets(),
                site: $site,
            );

            return $this->formSuccess($params, $submission);
        } catch (SilentFormFailureException $e) {
            return $this->formSuccess($params, $e->submission(), silentFailure: true);
        } catch (ValidationException $e) {
            return $this->formFailure($params, $e->errors(), $form->handle());
        }
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

        $response = $redirect && ! \Statamic\Facades\URL::isExternalToApplication($redirect)
            ? redirect($redirect)
            : back();

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
        if ($redirect = Form::getSubmissionRedirect($submission)) {
            return $redirect;
        }

        $redirect = Arr::get($params, '_redirect');

        if ($redirect && \Statamic\Facades\URL::isExternalToApplication($redirect)) {
            return null;
        }

        return $redirect;
    }
}

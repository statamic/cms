<?php

namespace Statamic\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\MessageBag;
use Illuminate\Validation\ValidationException;
use Statamic\Contracts\Forms\Submission;
use Statamic\Exceptions\SilentFormFailureException;
use Statamic\Facades\Form;
use Statamic\Forms\Exceptions\FileContentTypeRequiredException;
use Statamic\Forms\SubmitForm;
use Statamic\Support\Arr;
use Statamic\Support\Str;
use Statamic\Support\Traits\Hookable;

use function Statamic\trans as __;

class FormController extends Controller
{
    use Hookable;

    public function submit(Request $request, $form, SubmitForm $action)
    {
        $this->validateContentType($request, $form);

        $action->form($form);

        $params = $this->params($request);

        try {
            // Precognition is reimplemented here now that FrontendFormRequest is gone.
            // We validate (scoped to the requested fields) through the action and halt
            // without persisting, mirroring Laravel's FormRequest precognition.
            if ($request->isPrecognitive()) {
                $action->validate($request->all(), $request->allFiles(), only: $this->precognitiveFields($request));

                return response()->noContent(headers: ['Precognition-Success' => 'true']);
            }

            // Forms Pro uses this hook to resume an in-progress multi-page (partial) submission.
            $this->runHooks('submitting', [
                'request' => $request,
                'form' => $form,
                'action' => $action,
            ]);

            $submission = $action->submit($request->all(), $request->allFiles());

            return $this->formSuccess($params, $submission);
        } catch (SilentFormFailureException $e) {
            return $this->formSuccess($params, $e->submission(), silentFailure: true);
        } catch (ValidationException $e) {
            return $this->formFailure($params, $e->errors(), $form->handle());
        }
    }

    private function params(Request $request): array
    {
        return collect($request->all())
            ->filter(fn ($value, string $key) => Str::startsWith($key, '_'))
            ->all();
    }

    private function precognitiveFields(Request $request): ?array
    {
        if (! $request->headers->has('Precognition-Validate-Only')) {
            return null;
        }

        return explode(',', $request->header('Precognition-Validate-Only'));
    }

    private function validateContentType(Request $request, $form): void
    {
        $type = Str::before($request->headers->get('CONTENT_TYPE'), ';');

        if ($type !== 'multipart/form-data' && $form->hasFiles() && $request->allFiles()) {
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

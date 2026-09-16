<?php

namespace Statamic\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\MessageBag;
use Illuminate\Support\Uri;
use Illuminate\Validation\ValidationException;
use Statamic\Contracts\Forms\Submission;
use Statamic\Exceptions\FormRestrictedException;
use Statamic\Exceptions\SilentFormFailureException;
use Statamic\Facades\Form;
use Statamic\Facades\URL;
use Statamic\Forms\Exceptions\FileContentTypeRequiredException;
use Statamic\Forms\SubmissionResult;
use Statamic\Forms\SubmitForm;
use Statamic\Support\Arr;
use Statamic\Support\Str;

use function Statamic\trans as __;

class FormController extends Controller
{
    public function submit(Request $request, $form, SubmitForm $action)
    {
        $this->validateContentType($request, $form);

        $params = $this->params($request);

        $action->form($form);

        if ($form->hasMultiplePages()) {
            $action->page($form->formFields()->pages()->first()['id']);

            if ($partialSubmission = $this->getPartialSubmission($form)) {
                $action->resume($partialSubmission);
            }

            if (
                ($page = $request->input('_page')) &&
                $form->formFields()->pages()->where('id', $page)->isNotEmpty()
            ) {
                $action->page($page);
            }
        }

        try {
            // We validate (scoped to the requested fields) through the action and halt
            // without persisting, mirroring how Precognition works in Form Requests.
            if ($request->isPrecognitive()) {
                $action->validate($request->all(), $request->allFiles(), only: $this->precognitiveFields($request));

                return response()->noContent(headers: ['Precognition-Success' => 'true']);
            }

            $result = $action->submit($request->all(), $request->allFiles());

            $result->isFinalized()
                ? $this->forgetPartialSubmission($form)
                : $this->setPartialSubmission($form, $result->submission);

            return $this->formSuccess($params, $result);
        } catch (FormRestrictedException $e) {
            return $this->formFailure($params, ['*' => [$e->getMessage()]], $form->handle());
        } catch (SilentFormFailureException $e) {
            $result = new SubmissionResult(submission: $action->submission());

            return $this->formSuccess($params, $result, silentFailure: true);
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
     * @return Response|RedirectResponse
     */
    private function formFailure(array $params, array $errors, string $form)
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

        $response = $redirect && ! URL::isExternalToApplication($redirect)
            ? redirect($redirect)
            : back();

        return $response->withInput()->withErrors($errors, 'form.'.$form);
    }

    /**
     * The steps for a successful form submission.
     *
     * Used for actual success and by honeypot.
     *
     * @return Response
     */
    private function formSuccess(array $params, SubmissionResult $result, bool $silentFailure = false)
    {
        $submission = $result->submission;

        $redirect = $result->nextPage
            ? Uri::of($this->previousUrl())->withQuery(['page' => $result->nextPage])->__toString()
            : $this->formSuccessRedirect($params, $result->submission);

        if (request()->ajax() || request()->wantsJson()) {
            return response([
                'success' => true,
                'submission_created' => ! $silentFailure,
                'submission' => $submission->data(),
                'redirect' => $redirect,
                'next_page' => $result->nextPage,
            ]);
        }

        if (! $redirect) {
            $redirect = Uri::of($this->previousUrl())->withoutQuery('page')->__toString();
        }

        if (! $result->nextPage && ! URL::isExternal($redirect)) {
            session()->flash("form.{$submission->form()->handle()}.success", __('Submission successful.'));
            session()->flash("form.{$submission->form()->handle()}.submission_created", ! $silentFailure);
            session()->flash('submission', $submission);
        }

        return redirect($redirect);
    }

    private function previousUrl(): string
    {
        $previous = url()->previous();

        return URL::isExternalToApplication($previous) ? url('/') : $previous;
    }

    private function formSuccessRedirect(array $params, $submission)
    {
        if ($redirect = Form::getSubmissionRedirect($submission)) {
            return $redirect;
        }

        $redirect = Arr::get($params, '_redirect');

        if ($redirect && URL::isExternalToApplication($redirect)) {
            return null;
        }

        return $redirect;
    }

    private function setPartialSubmission($form, $submission): void
    {
        session()->put("form.{$form->handle()}.partial_submission", $submission->id());
    }

    private function getPartialSubmission($form): ?Submission
    {
        $id = session()->get("form.{$form->handle()}.partial_submission");
        $submission = $form->submission($id);

        if ($submission && ! $submission->isPartial()) {
            return null;
        }

        return $submission;
    }

    private function forgetPartialSubmission($form): void
    {
        session()->forget("form.{$form->handle()}.partial_submission");
    }
}

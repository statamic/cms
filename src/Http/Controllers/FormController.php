<?php

namespace Statamic\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\MessageBag;
use Illuminate\Validation\ValidationException;
use Statamic\Contracts\Forms\Submission;
use Statamic\Events\FormSubmitted;
use Statamic\Events\SubmissionCreated;
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
    public function submit(Request $request, $form)
    {
        $params = collect($request->all())->filter(function ($value, $key) {
            return Str::startsWith($key, '_');
        })->all();

        $fields = $form->blueprint()->fields()->addValues($values = $request->all());

        $submission = $form->makeSubmission()->data($values);

        try {
            $fields->validate();

            throw_if(Arr::get($values, $form->honeypot()), new SilentFormFailureException);

            $submission->uploadFiles();

            // If any event listeners return false, we'll do a silent failure.
            // If they want to add validation errors, they can throw an exception.
            if (FormSubmitted::dispatch($submission) === false) {
                throw new SilentFormFailureException;
            }
        } catch (ValidationException $e) {
            return $this->formFailure($params, $e->errors(), $form->handle());
        } catch (SilentFormFailureException $e) {
            return $this->formSuccess($params, $submission);
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
}

<?php

namespace Statamic\Addons\Form;

use Carbon\Carbon;
use Statamic\API\Form;
use Statamic\API\Crypt;
use Statamic\API\Email;
use Statamic\API\Parse;
use Statamic\API\Config;
use Statamic\API\Request;
use Statamic\Extend\Listener;
use Illuminate\Http\Response;
use Illuminate\Support\MessageBag;
use Illuminate\Http\RedirectResponse;
use Statamic\Contracts\Forms\Submission;
use Statamic\Exceptions\PublishException;
use Statamic\Exceptions\SilentFormFailureException;

class FormListener extends Listener
{
    public $events = [
        'Form.create' => 'create',
        'Form.submission.created' => 'sendEmails'
    ];

    /**
     * Handle a create form submission request
     *
     * @return mixed
     */
    public function create()
    {
        $fields = Request::all();

        if (! $params = Request::input('_params')) {
            return response('Invalid request.', 400);
        }

        $params = Crypt::decrypt($params);
        unset($fields['_params']);
        $formset = array_get($params, 'formset');

        $form = Form::get($formset);

        $submission = $form->createSubmission();

        try {
            $submission->data($fields);
            $submission->uploadFiles();

            // Allow addons to prevent the submission of the form, return
            // their own errors, and modify the submission.
            list($errors, $submission) = $this->runCreatingEvent($submission);
        } catch (PublishException $e) {
            return $this->formFailure($params, $e->getErrors(), $formset);
        } catch (SilentFormFailureException $e) {
            return $this->formSuccess($params, $submission);
        }

        if ($errors) {
            return $this->formFailure($params, $errors, $formset);
        }

        $submission->save();

        // Emit an event after the submission has been created.
        $this->emitEvent('submission.created', $submission);

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
                'submission' => $submission->data()
            ]);
        }

        $redirect = array_get($params, 'redirect');

        $response = ($redirect) ? redirect($redirect) : back();

        $this->flash->put("form.{$submission->formset()->name()}.success", true);
        $this->flash->put('submission', $submission);

        return $response;
    }

    /**
     * The steps for a failed form submission.
     *
     * @param array $params
     * @param array $submission
     * @param string $formset
     * @return Response|RedirectResponse
     */
    private function formFailure($params, $errors, $formset)
    {
        if (request()->ajax()) {
            return response([
                'errors' => (new MessageBag($errors))->all()
            ], 400);
        }

        // Set up where to be taken in the event of an error.
        if ($error_redirect = array_get($params, 'error_redirect')) {
            $error_redirect = redirect($error_redirect);
        } else {
            $error_redirect = back();
        }

        return $error_redirect->withInput()->withErrors($errors, 'form.'.$formset);
    }

    /**
     * Trigger sending of emails after a form submission.
     *
     * @param  Submission $submission
     */
    public function sendEmails(Submission $submission)
    {
        $config = $submission->formset()->get('email', []);

        // Ensure its an array of emails
        $config = (isset($config['to'])) ? [$config] : $config;

        foreach ($config as $c) {
            $this->sendEmail($submission, $c);
        }
    }

    /**
     * Send an email
     *
     * @param  Submission $submission
     * @param  array      $config
     * @return void
     */
    private function sendEmail(Submission $submission, array $config)
    {
        $email = Email::create();

        $config = $this->parseConfig($config, $submission->toArray());

        $email->to($config['to']);

        if ($from = array_get($config, 'from')) {
            $email->from($from);
        }

        if ($reply_to = array_get($config, 'reply_to')) {
            $email->replyTo($reply_to);
        }

        if ($cc = array_get($config, 'cc')) {
            $email->cc($cc);
        }

        if ($bcc = array_get($config, 'bcc')) {
            $email->bcc($bcc);
        }

        if ($subject = array_get($config, 'subject')) {
            $email->subject($subject);
        }

        if ($template = array_get($config, 'template')) {
            $email->template($template)
                ->with(array_merge($this->loadKeyVars(), $submission->toArray()));

        } else {
            $email->automagic()->with($submission->toArray());
        }

        $email->send();
    }

    /**
     * Parse the config values as templates so submission values may be used within them.
     *
     * @param  array  $config
     * @param  array  $data
     * @return array
     */
    private function parseConfig(array $config, array $data)
    {
        foreach ($config as $key => &$value) {
            $value = Parse::template(Parse::env($value), $data);
        }

        return $config;
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

        $responses = $this->emitEvent('submission.creating', $submission);

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

    /**
     * Bring in a couple common global vars that might be needed in the email template
     *
     * @return array
     */
    private function loadKeyVars()
    {
        return [
                'site_url'   => Config::getSiteUrl(),
                'date'       => Carbon::now(),
                'now'        => Carbon::now(),
                'today'      => Carbon::now(),
                'locale'     => site_locale()
        ];
    }
}

<?php

namespace Statamic\Forms\Listeners;

use Carbon\Carbon;
use Statamic\API\Form;
use Statamic\API\Email;
use Statamic\API\Parse;
use Statamic\API\Config;
use Statamic\Contracts\Forms\Submission;

class SendEmails
{
    /**
     * Trigger sending of emails after a form submission.
     *
     * @param  Submission $submission
     */
    public function handle(Submission $submission)
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

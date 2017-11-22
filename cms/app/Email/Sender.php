<?php

namespace Statamic\Email;

use Statamic\API\File;
use Statamic\API\Path;
use Statamic\API\Parse;
use Statamic\API\Config;
use Illuminate\Contracts\Mail\Mailer;

class Sender
{
    /**
     * @var \Illuminate\Contracts\Mail\Mailer
     */
    protected $mailer;

    /**
     * @var string|null
     */
    protected $location;

    /**
     * @var \Statamic\Email\Message
     */
    protected $message;

    /**
     * @param \Illuminate\Contracts\Mail\Mailer $mailer
     */
    public function __construct(Mailer $mailer)
    {
        $this->mailer = $mailer;
    }

    public function send(Message $message)
    {
        $this->message = $message;

        if ($location = $message->templatePath()) {
            $this->location = $location;
        }

        list($html_body, $text_body) = $this->parseEmailTemplate();

        $data = ['html_body' => $html_body];
        $view = 'email.html';

        if ($text_body) {
            $data['text_body'] = $text_body;
            $view = [$view, 'email.text'];
        }

        $this->mailer->send($view, $data, function ($m) {
            foreach (['to', 'cc', 'bcc'] as $action) {
                foreach ($this->message->$action() as $address) {
                    $m->$action($address[0], $address[1]);
                }
            }

            if (empty($from = $this->message->from())) {
                // If $from is an empty array, it means no email was specified, so we'll fall back to the defaults.
                $from = [Config::get('email.from_email'), Config::get('email.from_name')];
            }
            $m->from($from[0], $from[1]);

            if (! empty($reply = $this->message->replyTo())) {
                $m->replyTo($reply[0], $reply[1]);
            }

            $m->subject($this->message->subject());
        });
    }

    /**
     * Parses an email template
     *
     * @return array
     * @todo Support front-matter in templates.
     */
    private function parseEmailTemplate()
    {
        // If an automagic email is expected, we'll take a detour.
        if ($this->message->automagic()) {
            return $this->getAutomagicEmail();
        }

        $disk = $this->location ? null : 'theme';

        $location = $this->location ?: 'templates/email/';

        $path = Path::assemble($location, $this->message->template() . '.html');

        if (! File::disk($disk)->exists($path)) {
            return $this->parseFallbackEmailTemplates();
        }

        $raw_template = File::disk($disk)->get($path);

        // Split out the text version and the html versions
        $separator = Config::get('theming.email_separator', '---');
        $split = preg_split("#".PHP_EOL.$separator.PHP_EOL."#", $raw_template);
        $text = $split[0];
        $html = array_get($split, 1);

        // Parse the templates
        $html = Parse::template($html, $this->message->data());
        if ($text) {
            $text = Parse::template($text, $this->message->data());
        }

        return [$html, $text];
    }

    /**
     * Parses Blade views
     *
     * @return array
     */
    private function parseFallbackEmailTemplates()
    {
        $html_view = 'email.'.$this->message->template().'-html';
        $text_view = 'email.'.$this->message->template().'-text';

        return [
            view($html_view, $this->message->data())->__toString(),
            view($text_view, $this->message->data())->__toString()
        ];
    }

    private function getAutomagicEmail()
    {
        $html = '';
        $text = '';

        foreach ($this->message->data() as $key => $value) {
            $value = is_array($value) ? json_encode($value) : $value;
            $html .= "<strong>" . $key . "</strong>: " . $value . "<br><br>".PHP_EOL;
            $text .= $key . ": " . $value . PHP_EOL;
        }

        return [$html, $text];
    }
}

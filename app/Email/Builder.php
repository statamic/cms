<?php

namespace Statamic\Email;

use Statamic\API\Str;

class Builder
{
    /**
     * @var \Statamic\Email\Message
     */
    protected $message;

    /**
     * @param \Statamic\Email\Message $message
     */
    public function __construct(Message $message)
    {
        $this->message = $message;
    }

    /**
     * @param string $address
     * @param string|null $name
     * @return $this
     */
    public function from($address, $name = null)
    {
        $this->message->from($address, $name);

        return $this;
    }

    /**
     * @param string $address
     * @param string|null $name
     * @return $this
     */
    public function to($address, $name = null)
    {
        if (! $name) {
            $this->addAddresses('to', $address);
        } else {
            $this->message->to($address, $name);
        }

        return $this;
    }

    /**
     * @param string $address
     * @param string|null $name
     * @return $this
     */
    public function cc($address, $name = null)
    {
        if (! $name) {
            $this->addAddresses('cc', $address);
        } else {
            $this->message->cc($address, $name);
        }

        return $this;
    }

    /**
     * @param string $address
     * @param string|null $name
     * @return $this
     */
    public function bcc($address, $name = null)
    {
        if (! $name) {
            $this->addAddresses('bcc', $address);
        } else {
            $this->message->bcc($address, $name);
        }

        return $this;
    }

    public function replyTo($address, $name = null)
    {
        $this->message->replyTo($address, $name);

        return $this;
    }

    public function subject($subject)
    {
        $this->message->subject($subject);

        return $this;
    }

    public function template($template)
    {
        $this->message->template($template);

        return $this;
    }

    public function in($path)
    {
        $this->message->templatePath($path);

        return $this;
    }

    public function with($data)
    {
        $this->message->data($data);

        return $this;
    }

    public function automagic($automagic = true)
    {
        $this->message->automagic($automagic);

        return $this;
    }

    /**
     * Send the email
     */
    public function send()
    {
        $this->message->send();
    }

    public function message()
    {
        return $this->message;
    }

    /**
     * Add addresses to a given field
     *
     * @param string $field      The field: to, from, cc, bcc.
     * @param string $addresses  The email string to be parsed. It can contain multiple comma delimited
     *                           emails, either on their own or using the "Name <email>" format.
     * @return void
     */
    private function addAddresses($field, $addresses)
    {
        foreach ($this->parseAddresses($addresses) as $address) {
            $this->message->$field($address['email'], $address['name']);
        }
    }

    /**
     * Parse email addresses and names
     *
     * @param string $addresses  The email string to be parsed. It can contain multiple comma delimited
     *                           emails, either on their own or using the "Name <email>" format.
     * @return array             An associative array containing "email" and "name" keys. The names may be null.
     */
    private function parseAddresses($addresses)
    {
        return collect(explode(',', $addresses))->map(function ($address) {
            $name = null;
            $email = trim($address);

            if (Str::contains($email, '<')) {
                preg_match('/^(.*) \<(.*)\>$/', $email, $matches);
                $name = $matches[1];
                $email = $matches[2];
            }

            return [
                'email' => $email,
                'name' => $name
            ];
        })->all();
    }
}

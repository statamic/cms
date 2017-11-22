<?php

namespace Statamic\Email;

class Message
{
    /**
     * @var \Statamic\Email\Sender
     */
    protected $sender;

    /**
     * @var array
     */
    protected $from = [];

    /**
     * @var array
     */
    protected $to = [];

    /**
     * @var array
     */
    protected $cc = [];

    /**
     * @var array
     */
    protected $bcc = [];

    /**
     * @var array
     */
    protected $reply_to = [];

    /**
     * @var string
     */
    protected $subject;

    /**
     * @var string
     */
    protected $template;

    /**
     * @var string
     */
    protected $template_path;

    /**
     * @var array
     */
    protected $data = [];

    /**
     * @var bool
     */
    protected $automagic = false;

    /**
     * @param \Statamic\Email\Sender $sender
     */
    public function __construct(Sender $sender)
    {
        $this->sender = $sender;
    }

    /**
     * @param string|null $address
     * @param string|null $name
     * @return $this
     */
    public function from($address = null, $name = null)
    {
        if (! $address) {
            return $this->from;
        }

        $this->from = [$address, $name];

        return $this;
    }

    /**
     * @param string|null $address
     * @param string|null $name
     * @return $this
     */
    public function to($address = null, $name = null)
    {
        if (! $address) {
            return $this->to;
        }

        if (is_array($address)) {
            $this->to = [$address, $name];
        } else {
            $this->to[] = [$address, $name];
        }

        return $this;
    }

    /**
     * @param string|null $address
     * @param string|null $name
     * @return $this
     */
    public function cc($address = null, $name = null)
    {
        if (! $address) {
            return $this->cc;
        }

        if (is_array($address)) {
            $this->cc = [$address, $name];
        } else {
            $this->cc[] = [$address, $name];
        }

        return $this;
    }

    /**
     * @param string|null $address
     * @param string|null $name
     * @return $this
     */
    public function bcc($address = null, $name = null)
    {
        if (! $address) {
            return $this->bcc;
        }

        if (is_array($address)) {
            $this->bcc = [$address, $name];
        } else {
            $this->bcc[] = [$address, $name];
        }

        return $this;
    }

    public function replyTo($address = null, $name = null)
    {
        if (! $address) {
            return $this->reply_to;
        }

        $this->reply_to = [$address, $name];

        return $this;
    }

    public function subject($subject = null)
    {
        if (! $subject) {
            return $this->subject;
        }

        $this->subject = $subject;

        return $this;
    }

    public function template($template = null)
    {
        if (! $template) {
            return $this->template;
        }

        $this->template = $template;

        return $this;
    }

    public function templatePath($path = null)
    {
        if (! $path) {
            return $this->template_path;
        }

        $this->template_path = $path;

        return $this;
    }

    public function data($data = null)
    {
        if (! $data) {
            return $this->data;
        }

        $this->data = $data;

        return $this;
    }

    public function automagic($automagic = null)
    {
        if (is_null($automagic)) {
            return $this->automagic;
        }

        $this->automagic = $automagic;
    }

    /**
     * Send the email
     */
    public function send()
    {
        $this->sender->send($this);
    }
}

<?php

namespace Statamic\Forms;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Statamic\Facades\Config;
use Statamic\Facades\Parse;
use Statamic\Facades\Site;
use Statamic\Support\Str;

class Email extends Mailable
{
    use Queueable, SerializesModels;

    protected $submission;
    protected $config;

    public function __construct(Submission $submission, array $config)
    {
        $this->submission = $submission;
        $this->config = $this->parseConfig($config);
    }

    public function build()
    {
        $this
            ->subject($this->config['subject'] ?? __('Form Submission'))
            ->addAddresses()
            ->addViews()
            ->addData();
    }

    protected function addAddresses()
    {
        $this->to($this->addresses(array_get($this->config, 'to')));

        if ($from = array_get($this->config, 'from')) {
            $this->from($this->addresses($from));
        }

        if ($replyTo = array_get($this->config, 'reply_to')) {
            $this->replyTo($this->addresses($replyTo));
        }

        if ($cc = array_get($this->config, 'cc')) {
            $this->cc($this->addresses($cc));
        }

        if ($bcc = array_get($this->config, 'bcc')) {
            $this->bcc($this->addresses($bcc));
        }

        return $this;
    }

    protected function addViews()
    {
        $html = array_get($this->config, 'html');
        $text = array_get($this->config, 'text');

        if (! $text && ! $html) {
            return $this->automagic();
        }

        if ($text) {
            $this->text($text);
        }

        if ($html) {
            $this->view($html);
        }

        return $this;
    }

    protected function addData()
    {
        $data = array_merge($this->submission->toArray(), [
            'site_url'   => Config::getSiteUrl(),
            'date'       => now(),
            'now'        => now(),
            'today'      => now(),
            'site'       => $site = Site::current()->handle(),
            'locale'     => $site,
        ]);

        return $this->with($data);
    }

    protected function automagic()
    {
        $html = collect($this->submission->toArray())->map(function ($value, $key) {
            $value = is_array($value) ? json_encode($value) : $value;

            return "<b>{$key}:</b> {$value}";
        })->implode("<br>\n");

        return $this->html($html);
    }

    protected function addresses($addresses)
    {
        if (! $addresses) {
            return;
        }

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
                'name' => $name,
            ];
        })->all();
    }

    protected function parseConfig(array $config)
    {
        $data = $this->submission->toArray();

        return collect($config)->map(function ($value) use ($data) {
            return (string) Parse::template(Parse::env($value), $data);
        });
    }
}

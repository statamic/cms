<?php

namespace Statamic\Forms\Captcha;

class Turnstile extends SiteverifyCaptcha
{
    public function field(): string
    {
        return 'cf-turnstile-response';
    }

    protected function endpoint(): string
    {
        return 'https://challenges.cloudflare.com/turnstile/v0/siteverify';
    }

    public function widget(): string
    {
        return '<div class="cf-turnstile" data-captcha data-sitekey="'.$this->attr($this->siteKey()).'"></div>'
            .'<script src="https://challenges.cloudflare.com/turnstile/v0/api.js" async defer></script>';
    }
}

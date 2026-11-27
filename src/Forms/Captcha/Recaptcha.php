<?php

namespace Statamic\Forms\Captcha;

/**
 * Google reCAPTCHA v2, the "I'm not a robot" checkbox.
 */
class Recaptcha extends SiteverifyCaptcha
{
    public function field(): string
    {
        return 'g-recaptcha-response';
    }

    protected function endpoint(): string
    {
        return 'https://www.google.com/recaptcha/api/siteverify';
    }

    protected function markup(): string
    {
        return '<div class="g-recaptcha" data-captcha data-sitekey="'.$this->attr($this->siteKey()).'"></div>'
            .'<script'.$this->nonce().' src="https://www.google.com/recaptcha/api.js" async defer></script>';
    }
}

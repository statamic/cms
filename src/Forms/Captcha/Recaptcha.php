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

    public function widget(): string
    {
        return '<div class="g-recaptcha" data-captcha data-sitekey="'.$this->attr($this->siteKey()).'"></div>'
            .'<script src="https://www.google.com/recaptcha/api.js" async defer></script>';
    }
}

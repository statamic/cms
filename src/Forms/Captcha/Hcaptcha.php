<?php

namespace Statamic\Forms\Captcha;

class Hcaptcha extends SiteverifyCaptcha
{
    public function field(): string
    {
        return 'h-captcha-response';
    }

    protected function endpoint(): string
    {
        return 'https://api.hcaptcha.com/siteverify';
    }

    protected function payload(string $token, ?string $ip): array
    {
        return parent::payload($token, $ip) + ['sitekey' => $this->siteKey()];
    }

    public function widget(): string
    {
        return '<div class="h-captcha" data-captcha data-sitekey="'.$this->attr($this->siteKey()).'"></div>'
            .'<script src="https://js.hcaptcha.com/1/api.js" async defer></script>';
    }
}

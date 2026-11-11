<?php

namespace Statamic\Forms\Captcha;

/**
 * Google reCAPTCHA v3. There's nothing to tick: Google scores each visit
 * from 0 (a bot) to 1 (a person). Visits scoring under the "score" option
 * are refused, and so are tokens made for another action, so a token
 * minted for one purpose can't be replayed somewhere else.
 */
class RecaptchaV3 extends Recaptcha
{
    public function widget(): string
    {
        $key = $this->attr($this->siteKey());

        // Asks Google for a token as the form is submitted, then submits it again. The ready()
        // stub is Google's documented way to queue the call until the async script has loaded.
        return '<input type="hidden" name="g-recaptcha-response" value="" data-captcha data-captcha-recaptcha>'
            .'<script src="https://www.google.com/recaptcha/api.js?render='.$key.'" async defer></script>'
            .'<script>(function(){if(typeof grecaptcha==="undefined"){grecaptcha={}}grecaptcha.ready=grecaptcha.ready||function(cb){var c="___grecaptcha_cfg";window[c]=window[c]||{};(window[c].fns=window[c].fns||[]).push(cb)};document.querySelectorAll("[data-captcha-recaptcha]").forEach(function(input){var form=input.form;if(!form||form.dataset.captchaRecaptcha)return;form.dataset.captchaRecaptcha=1;form.addEventListener("submit",function(event){if(input.value)return;event.preventDefault();grecaptcha.ready(function(){grecaptcha.execute('.$this->js($this->siteKey()).',{action:'.$this->js($this->action()).'}).then(function(token){input.value=token;form.requestSubmit?form.requestSubmit():form.submit()})})})})})();</script>';
    }

    public function action(): string
    {
        $action = $this->config['action'] ?? null;

        // Google allows letters, digits, slashes and underscores.
        return is_string($action) && preg_match('#^[A-Za-z0-9/_]+$#', $action) ? $action : 'submit';
    }

    public function score(): float
    {
        $score = $this->config['score'] ?? null;

        return is_numeric($score) ? (float) $score : 0.5;
    }

    protected function passed(array $result): bool
    {
        return parent::passed($result)
            && ($result['action'] ?? null) === $this->action()
            && is_numeric($result['score'] ?? null)
            && (float) $result['score'] >= $this->score();
    }
}

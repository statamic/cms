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
    protected function markup(): string
    {
        $src = 'https://www.google.com/recaptcha/api.js?render='.rawurlencode($this->siteKey());

        // Gets a token from Google as the form is submitted, then submits it again. The listener is on
        // the document, in the capture phase, so it runs before any other submit handlers, such as one
        // sending the form with fetch(), and they only see the submission that carries the token. Each
        // token is used once, as Google only accepts it once. If Google can't be reached, the form is
        // still submitted, so the visitor sees the error instead of a button that does nothing. The
        // ready() stub is Google's documented way to queue the call until the async script has loaded.
        return '<input type="hidden" name="g-recaptcha-response" value="" data-captcha data-captcha-recaptcha>'
            .'<script'.$this->nonce().' src="'.$this->attr($src).'" async defer></script>'
            .'<script'.$this->nonce().'>(function(){'
            .'if(window.statamicRecaptchaV3)return;window.statamicRecaptchaV3=1;'
            .'if(typeof grecaptcha==="undefined"){grecaptcha={}}'
            .'grecaptcha.ready=grecaptcha.ready||function(cb){var c="___grecaptcha_cfg";window[c]=window[c]||{};(window[c].fns=window[c].fns||[]).push(cb)};'
            .'document.addEventListener("submit",function(event){'
            .'var form=event.target,input=form.querySelector&&form.querySelector("[data-captcha-recaptcha]");'
            .'if(!input)return;'
            .'var minted=Number(input.dataset.captchaMinted||0);delete input.dataset.captchaMinted;'
            .'if(Date.now()-minted<90000)return;'
            .'event.preventDefault();event.stopImmediatePropagation();'
            .'var submitter=event.submitter;'
            .'function send(token){input.value=token;input.dataset.captchaMinted=Date.now();form.requestSubmit?form.requestSubmit(submitter&&submitter.form===form?submitter:undefined):form.submit()}'
            .'grecaptcha.ready(function(){grecaptcha.execute('.$this->js($this->siteKey()).',{action:'.$this->js($this->action()).'}).then(send,function(){send("")})})'
            .'},true)'
            .'})();</script>';
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

<?php

namespace Statamic\Contracts\Forms;

interface Captcha
{
    /**
     * The HTML that goes inside the form: the widget and its script.
     *
     * The outermost element should carry a `data-captcha` attribute, so
     * the form tag can tell the template has already rendered it.
     */
    public function widget(): string;

    /**
     * Whether the visitor passed, given the token the widget put in the form.
     *
     * A failed check, or a provider that can't be reached, should return false.
     */
    public function verify(?string $token, ?string $ip = null): bool;

    /**
     * The name of the form input the widget fills with its token.
     */
    public function field(): string;
}

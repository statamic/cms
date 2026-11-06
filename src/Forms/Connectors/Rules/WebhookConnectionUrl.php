<?php

namespace Statamic\Forms\Connectors\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Statamic\Exceptions\InvalidRemoteUrlException;
use Statamic\Forms\Connectors\Webhooks\SendWebhook;

class WebhookConnectionUrl implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (blank($value) || ! is_string($value) || app()->environment('local')) {
            return;
        }

        try {
            SendWebhook::resolve($value);
        } catch (InvalidRemoteUrlException) {
            $fail('statamic::validation.webhook_url_not_public')->translate();
        }
    }
}

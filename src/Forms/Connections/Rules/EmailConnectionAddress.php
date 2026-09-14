<?php

namespace Statamic\Forms\Connections\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Statamic\Contracts\Forms\Form;
use Statamic\Support\Arr;
use Statamic\Support\Str;

class EmailConnectionAddress implements ValidationRule
{
    public function __construct(private Form $form)
    {
    }

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (blank($value)) {
            return;
        }

        foreach (Arr::wrap($value) as $entry) {
            $this->validateEntry($entry, $fail);
        }
    }

    private function validateEntry(mixed $value, Closure $fail): void
    {
        if (! is_string($value)) {
            $fail('statamic::validation.email')->translate();

            return;
        }

        if (Str::contains($value, '{{')) {
            return;
        }

        if (Str::startsWith($value, 'field:')) {
            if (! $this->form->formFields()->fields()->has(Str::after($value, 'field:'))) {
                $fail('statamic::validation.email_field_reference')->translate();
            }

            return;
        }

        collect(explode(',', $value))
            ->map(fn ($address) => trim($address))
            ->filter()
            ->each(function ($email) use ($fail) {
                if (Str::contains($email, '<') && preg_match('/^(.*) \<(.*)\>$/', $email, $matches)) {
                    $email = $matches[2];
                }

                if (! filter_var($email, FILTER_VALIDATE_EMAIL)) {
                    $fail('statamic::validation.email')->translate();
                }
            });
    }
}

<?php

namespace Statamic\Forms\Connections\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Statamic\Support\Str;

class EmailConnectionView implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (blank($value)) {
            return;
        }

        if (! is_string($value) || Str::contains($value, '::') || ! $this->isInFolder($value)) {
            $fail('validation.in')->translate();
        }
    }

    private function isInFolder(string $view): bool
    {
        if (! $folder = trim(config('statamic.forms.email_view_folder') ?? '', '/')) {
            return true;
        }

        return Str::startsWith(str_replace('.', '/', $view), $folder.'/');
    }
}

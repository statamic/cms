<?php

namespace Statamic\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

/**
 * Like {@see Slug}, but allows `/`-separated path segments and `_index`
 * section pages. Used by Sidecar drivers (e.g. LaraDocs) whose content
 * uses nested filesystem paths as slugs.
 */
class PathSlug implements ValidationRule
{
    /**
     * A single slug segment: letters/numbers with optional dash/underscore
     * separators, or a section index page named `_index`.
     */
    private const SEGMENT = '(?:_index|[a-zA-Z0-9]+(?:[-_]{0,1}[a-zA-Z0-9])*)';

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! is_string($value) || ! preg_match('/^'.self::SEGMENT.'(?:\/'.self::SEGMENT.')*$/', $value)) {
            $fail('statamic::validation.path_slug')->translate();
        }
    }
}

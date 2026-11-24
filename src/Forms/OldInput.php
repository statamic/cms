<?php

namespace Statamic\Forms;

use Statamic\Support\Str;

class OldInput
{
    /**
     * Whether the old (flashed) input should be filled into a form's fields.
     *
     * Old input is flashed globally by Laravel and is not scoped to a form,
     * so it is attributed using the flashed error bags: a `form.{handle}` bag
     * is only ever flashed by that form's failed submission. Fill the old
     * input into every form, unless it can be attributed to a different
     * form's failed submission.
     */
    public static function shouldFill(string $errorBag): bool
    {
        $formBags = collect(session('errors')?->getBags() ?? [])
            ->keys()
            ->filter(fn ($bag) => Str::startsWith($bag, 'form.'));

        return $formBags->isEmpty() || $formBags->contains($errorBag);
    }
}

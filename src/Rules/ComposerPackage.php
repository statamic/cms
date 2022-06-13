<?php

namespace Statamic\Rules;

use Illuminate\Contracts\Validation\Rule;

class ComposerPackage implements Rule
{
    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
        return preg_match("/^[^\/\s]+\/[^\/\s]+$/", $value);
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return 'Please enter a valid composer package name (eg. hasselhoff/kung-fury).';
    }
}

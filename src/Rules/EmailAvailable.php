<?php

namespace Statamic\Rules;

use Illuminate\Contracts\Validation\Rule;
use Statamic\Facades\User;

class EmailAvailable implements Rule
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
        return User::query()->where('email', trim($value))->count() === 0;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return 'A user with this email already exists.';
    }
}

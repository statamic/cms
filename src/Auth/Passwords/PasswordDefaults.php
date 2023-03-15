<?php

namespace Statamic\Auth\Passwords;

use Illuminate\Validation\Rules\Password;

class PasswordDefaults
{
    /**
     * @return Password|string
     */
    public static function rules()
    {
        return Password::default();
    }
}

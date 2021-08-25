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
        if (version_compare(app()->version(), '8.43.0', '<')) {
            // Return the old password rules
            return 'min:8';
        }

        return Password::default();
    }
}

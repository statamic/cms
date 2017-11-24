<?php

namespace Statamic\Addons\Suggest\Modes;

use Statamic\API\User;

class UsersMode extends AbstractMode
{
    public function suggestions()
    {
        $suggestions = [];

        foreach (User::all() as $user) {
            $suggestions[$user->id()] = $this->label($user, 'username');
        }

        return format_input_options($suggestions);
    }
}

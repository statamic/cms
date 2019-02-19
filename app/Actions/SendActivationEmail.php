<?php

namespace Statamic\Actions;

use Statamic\API;

class SendActivationEmail extends Action
{
    public function visibleTo($key, $context)
    {
        return $key === 'users';
    }

    public function run($items)
    {
        //
    }
}

<?php

namespace Statamic\Policies;

use Statamic\Facades\User;

class SitePolicy
{
    public function view($user, $site)
    {
        return User::fromUser($user)->hasPermission("access {$site->handle()} site");
    }
}

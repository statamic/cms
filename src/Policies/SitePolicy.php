<?php

namespace Statamic\Policies;

use Statamic\Facades\User;

class SitePolicy
{
    public function index($user)
    {
        // handled by before()
    }

    public function view($user, $site)
    {
        return User::fromUser($user)->hasPermission("access {$site->handle()} site");
    }
}

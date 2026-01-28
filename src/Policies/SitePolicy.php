<?php

namespace Statamic\Policies;

use Statamic\Facades\Site;
use Statamic\Facades\User;

class SitePolicy
{
    public function before($user)
    {
        if (User::fromUser($user)->isSuper()) {
            return true;
        }
    }

    public function view($user, $site)
    {
        if (! Site::multiEnabled()) {
            return true;
        }

        return User::fromUser($user)->hasPermission("access {$site->handle()} site");
    }
}

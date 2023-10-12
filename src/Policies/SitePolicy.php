<?php

namespace Statamic\Policies;

use Statamic\Facades\Site;
use Statamic\Facades\User;

class SitePolicy
{
    public function view($user, $site)
    {
        if (! Site::hasMultiple()) {
            return true;
        }

        return User::fromUser($user)->hasPermission("access {$site->handle()} site");
    }
}

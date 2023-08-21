<?php

namespace Statamic\Policies;

use Statamic\Facades\Site;
use Statamic\Facades\User;

class SitePolicy
{
    public function before($user, $ability)
    {
        if (! Site::hasMultiple()) {
            return null;
        }

        $site = Site::selected();

        if (! User::fromUser($user)->hasPermission("access {$site->handle()} site")) {
            return false;
        }
    }

    public function index($user)
    {
        // handled by before()
    }

    public function view($user, $site)
    {
        return User::fromUser($user)->hasPermission("access {$site->handle()} site");
    }
}

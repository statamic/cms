<?php

namespace Statamic\Policies;

use Statamic\Facades\Site;
use Statamic\Facades\User;

class SitePolicy
{
    public function before($user, $ability)
    {
        $user = User::fromUser($user);
        $site = Site::selected();

        if (! $user->hasPermission("access {$site->handle()} site")) {
            return false;
        }
    }

    public function index($user)
    {
        // handled by before()
    }

    public function view($user, $site)
    {
        $user = User::fromUser($user);

        return $user->hasPermission("access {$site->handle()} site");
    }
}

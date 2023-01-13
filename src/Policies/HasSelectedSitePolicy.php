<?php

namespace Statamic\Policies;

use Statamic\Facades\Site;

trait HasSelectedSitePolicy
{
    protected function accessInSelectedSite($user, $data = null)
    {
        $site = Site::selected();

        return $user->hasPermission("access {$site->handle()} site")
            && (is_null($data) || $data->existsIn($site->handle()));
    }
}

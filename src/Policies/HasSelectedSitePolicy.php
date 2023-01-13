<?php

namespace Statamic\Policies;

use Statamic\Facades\Site;

trait HasSelectedSitePolicy
{
    protected function accessInSelectedSite($user, $arguments)
    {
        $site = Site::selected();

        // If this first argument is a string, that means they are passing a class name
        // to the policy. We will remove the first argument from this argument array.
        if (isset($arguments[0]) && is_string($arguments[0])) {
            array_shift($arguments);
        }

        $data = $arguments[0] ?? null;

        return $user->can("access {$site->handle()} site")
            && (is_null($data) || (method_exists($data, 'existsIn') && $data->existsIn($site->handle())));
    }
}

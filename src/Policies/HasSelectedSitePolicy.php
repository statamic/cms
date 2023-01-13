<?php

namespace Statamic\Policies;

use Statamic\Facades\Site;

trait HasSelectedSitePolicy
{
    protected function accessInSelectedSite($user, $arguments)
    {
        if (! Site::hasMultiple()) {
            return true;
        }

        $site = Site::selected();

        // If this first argument is a string, that means they are passing a class name
        // to the policy. We will remove the first argument from this argument array.
        if (isset($arguments[0]) && is_string($arguments[0])) {
            array_shift($arguments);
        }

        if (($data = $arguments[0] ?? null)
            && method_exists($data, 'existsIn')
            && ! $data->existsIn($site->handle())) {
            return false;
        }

        return $user->can("access {$site->handle()} site");
    }
}

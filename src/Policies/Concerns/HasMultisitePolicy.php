<?php

namespace Statamic\Policies\Concerns;

use Statamic\Facades\Site as Sites;
use Statamic\Sites\Site;

trait HasMultisitePolicy
{
    protected function userCanAccessSite($user, Site $site)
    {
        return $user->can('view', $site);
    }

    protected function userCanAccessAnySite($user, $sites)
    {
        if (! Sites::multiEnabled()) {
            return true;
        }

        return $sites
            ->map(fn ($site) => Sites::get($site))
            ->filter(fn ($site) => $user->can('view', $site))
            ->isNotEmpty();
    }
}

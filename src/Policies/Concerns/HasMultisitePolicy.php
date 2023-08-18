<?php

namespace Statamic\Policies\Concerns;

use Statamic\Facades\Site;

trait HasMultisitePolicy
{
    protected function siteIsForbidden($user, ...$arguments)
    {
        return $this->selectedSiteIsForbidden($user)
            || $this->dataHasNoAccessibleSite($user, $arguments);
    }

    protected function selectedSiteIsForbidden($user)
    {
        if (! Site::hasMultiple()) {
            return false;
        }

        $site = Site::selected();

        return $user->cant("access {$site->handle()} site");
    }

    protected function dataHasNoAccessibleSite($user, $arguments)
    {
        if (! $data = $this->getDataFromArguments($arguments)) {
            return false;
        }

        if (! method_exists($data, 'sites')) {
            return false;
        }

        return $data->sites()
            ->filter(fn ($site) => $user->can("access {$site} site"))
            ->isEmpty();
    }

    // TODO... Do we actually want this?
    //
    // protected function dataDoesNotExistInSelectedSite($data, $arguments)
    // {
    //     if (! $data = $this->getDataFromArguments($arguments)) {
    //         return false;
    //     }
    //
    //     if (! method_exists($data, 'existsIn')) {
    //         return false;
    //     }
    //
    //     return ! $data->existsIn(Site::selected()->handle());
    // }

    private function getDataFromArguments($arguments)
    {
        // If this first argument is a string, that means they are passing a class name
        // to the policy. We will remove the first argument from this argument array.
        if (isset($arguments[0]) && is_string($arguments[0])) {
            array_shift($arguments);
        }

        return $arguments[0] ?? null;
    }
}
